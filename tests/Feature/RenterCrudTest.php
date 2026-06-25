<?php

use App\Enums\MeterType;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\MeterReading;
use App\Models\RenterService;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function renterPayload(array $overrides = []): array
{
    return array_merge([
        'last_name' => 'Иванов',
        'name' => 'Иван',
        'middle_name' => 'Иванович',
        'login' => 'ivanov',
        'email' => 'ivanov@example.com',
        'phone' => '+375291234567',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $overrides);
}

test('guest cannot access renters page', function () {
    $this->get(route('renters.get'))->assertRedirect(route('login'));
});

test('renter cannot access renters page', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('renters.get'))
        ->assertForbidden();
});

test('admin can view renters page', function () {
    User::factory()->renter()->create(['last_name' => 'Петров', 'name' => 'Пётр']);

    $this->actingAs(adminUser())
        ->get(route('renters.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/renter')
            ->has('renters', 1));
});

test('admin can view renter settings page', function () {
    $renter = User::factory()->renter()->create(['last_name' => 'Петров', 'name' => 'Пётр']);

    $this->actingAs(adminUser())
        ->get(route('renters.settings', $renter->id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/renter/settings')
            ->has('renter')
            ->has('rooms')
            ->has('services')
            ->has('initialMeterReadings')
            ->where('contract', null));
});

test('admin can assign renter to room', function () {
    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'number' => '101',
        'status' => RoomStatus::Free,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $room->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    expect($room->fresh()->user_id)->toBe($renter->id);
    expect($room->fresh()->status)->toBe(RoomStatus::Occupied);
});

test('admin can remove renter from room', function () {
    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => null,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    expect($room->fresh()->user_id)->toBeNull();
    expect($room->fresh()->status)->toBe(RoomStatus::Free);
});

test('admin cannot assign renter to room under repair', function () {
    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'status' => RoomStatus::Repair,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $room->id,
        ])
        ->assertSessionHasErrors('room_id');

    expect($room->fresh()->user_id)->toBeNull();
});

test('assigning renter to new room frees previous room', function () {
    $renter = User::factory()->renter()->create();
    $oldRoom = Room::factory()->create([
        'number' => '1',
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);
    $newRoom = Room::factory()->create([
        'number' => '2',
        'status' => RoomStatus::Free,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $newRoom->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    expect($oldRoom->fresh()->user_id)->toBeNull();
    expect($oldRoom->fresh()->status)->toBe(RoomStatus::Free);
    expect($newRoom->fresh()->user_id)->toBe($renter->id);
    expect($newRoom->fresh()->status)->toBe(RoomStatus::Occupied);
});

test('changing renter room deletes all services', function () {
    $renter = User::factory()->renter()->create();
    $oldRoom = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);
    $newRoom = Room::factory()->create(['status' => RoomStatus::Free]);
    $services = RenterService::factory()->count(2)->create(['user_id' => $renter->id]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $newRoom->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    foreach ($services as $service) {
        $this->assertDatabaseMissing('renter_services', ['id' => $service->id]);
    }
});

test('removing renter from room deletes all services', function () {
    $renter = User::factory()->renter()->create();
    Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);
    $service = RenterService::factory()->create(['user_id' => $renter->id]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => null,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $this->assertDatabaseMissing('renter_services', ['id' => $service->id]);
});

test('assigning renter to room for the first time keeps existing services', function () {
    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create(['status' => RoomStatus::Free]);
    $service = RenterService::factory()->create(['user_id' => $renter->id]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $room->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $this->assertDatabaseHas('renter_services', ['id' => $service->id]);
});

test('admin can create contract for renter', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->put(route('renters.contract', $renter->id), [
            'number' => 'ДГ-2026-001',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'monthly_rent' => 450.50,
            'notes' => 'Стандартный договор',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $this->assertDatabaseHas('contracts', [
        'user_id' => $renter->id,
        'number' => 'ДГ-2026-001',
        'monthly_rent' => 450.50,
    ]);
});

test('admin can update existing contract for renter', function () {
    $renter = User::factory()->renter()->create();
    $contract = Contract::factory()->create([
        'user_id' => $renter->id,
        'number' => 'ДГ-OLD',
        'monthly_rent' => 300,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.contract', $renter->id), [
            'number' => 'ДГ-NEW',
            'start_date' => $contract->start_date->format('Y-m-d'),
            'monthly_rent' => 500,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    expect($contract->fresh()->number)->toBe('ДГ-NEW');
    expect((float) $contract->fresh()->monthly_rent)->toBe(500.0);
});

test('admin can attach image to contract', function () {
    Storage::fake('public');

    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->put(route('renters.contract', $renter->id), [
            'number' => 'ДГ-2026-002',
            'start_date' => '2026-01-01',
            'monthly_rent' => 450.50,
            'file' => UploadedFile::fake()->create('contract.jpg', 100, 'image/jpeg'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $contract = Contract::query()->where('user_id', $renter->id)->first();

    expect($contract?->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($contract->file_path);
});

test('admin can attach pdf to contract', function () {
    Storage::fake('public');

    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->put(route('renters.contract', $renter->id), [
            'number' => 'ДГ-2026-003',
            'start_date' => '2026-01-01',
            'monthly_rent' => 450.50,
            'file' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $contract = Contract::query()->where('user_id', $renter->id)->first();

    expect($contract?->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($contract->file_path);
});

test('admin can remove contract file', function () {
    Storage::fake('public');

    $renter = User::factory()->renter()->create();
    $path = 'contracts/contract.jpg';
    Storage::disk('public')->put($path, 'fake-image-content');
    $contract = Contract::factory()->create([
        'user_id' => $renter->id,
        'file_path' => $path,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.contract', $renter->id), [
            'number' => $contract->number,
            'start_date' => $contract->start_date->format('Y-m-d'),
            'monthly_rent' => $contract->monthly_rent,
            'remove_file' => true,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    expect($contract->fresh()->file_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('admin can add service for renter', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->post(route('renters.services.store', $renter->id), [
            'name' => 'Интернет',
            'price' => 25.50,
            'notes' => '100 Мбит/с',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $this->assertDatabaseHas('renter_services', [
        'user_id' => $renter->id,
        'name' => 'Интернет',
        'price' => 25.50,
    ]);
});

test('admin can delete service for renter', function () {
    $renter = User::factory()->renter()->create();
    $service = RenterService::factory()->create(['user_id' => $renter->id]);

    $this->actingAs(adminUser())
        ->delete(route('renters.services.destroy', [$renter->id, $service->id]))
        ->assertRedirect(route('renters.settings', $renter->id));

    $this->assertDatabaseMissing('renter_services', ['id' => $service->id]);
});

test('deleting renter frees assigned room', function () {
    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    $this->actingAs(adminUser())
        ->delete(route('renters.delete', $renter->id))
        ->assertRedirect(route('renters.get'));

    expect($room->fresh()->user_id)->toBeNull();
    expect($room->fresh()->status)->toBe(RoomStatus::Free);
});

test('admin can create renter', function () {
    $this->actingAs(adminUser())
        ->post(route('renters.create'), renterPayload())
        ->assertRedirect(route('renters.get'));

    $this->assertDatabaseHas('users', [
        'email' => 'ivanov@example.com',
        'login' => 'ivanov',
        'role' => UserRole::RENTER->value,
    ]);
});

test('admin can update renter', function () {
    $renter = User::factory()->renter()->create([
        'last_name' => 'Сидоров',
        'name' => 'Сидор',
    ]);

    $this->actingAs(adminUser())
        ->from(route('renters.get'))
        ->put(route('renters.update', $renter->id), [
            'last_name' => 'Сидоров',
            'name' => 'Сергей',
            'middle_name' => null,
            'login' => $renter->login,
            'email' => $renter->email,
            'phone' => '+375331234567',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.get'));

    expect($renter->fresh()->name)->toBe('Сергей');
});

test('admin can delete renter', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->delete(route('renters.delete', $renter->id))
        ->assertRedirect(route('renters.get'));

    $this->assertDatabaseMissing('users', ['id' => $renter->id]);
});

test('admin cannot delete admin user via renter route', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->delete(route('renters.delete', $admin->id))
        ->assertSessionHasErrors('id');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin can save initial meter readings in renter settings', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->put(route('renters.initial-meter-readings', $renter->id), [
            'readings' => [
                'cold_water' => [
                    'value' => 100.5,
                    'reading_date' => '2026-01-01',
                ],
                'hot_water' => [
                    'value' => 50.25,
                    'reading_date' => '2026-01-01',
                ],
                'electricity' => [
                    'value' => 1000,
                    'reading_date' => '2026-01-01',
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $this->assertDatabaseHas('meter_readings', [
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater->value,
        'value' => 100.5,
        'is_initial' => true,
    ]);

    $this->assertDatabaseHas('meter_readings', [
        'user_id' => $renter->id,
        'type' => MeterType::Electricity->value,
        'value' => 1000,
        'is_initial' => true,
    ]);
});

test('admin can update initial meter readings', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'value' => 80,
        'reading_date' => '2026-01-01',
        'is_initial' => true,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.initial-meter-readings', $renter->id), [
            'readings' => [
                'cold_water' => [
                    'value' => 95,
                    'reading_date' => '2026-01-01',
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    expect(MeterReading::query()->where('user_id', $renter->id)->where('is_initial', true)->count())->toBe(1);
    expect((float) MeterReading::query()->where('user_id', $renter->id)->where('is_initial', true)->value('value'))->toBe(95.0);
});

test('admin can remove initial meter reading by clearing fields', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'is_initial' => true,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.initial-meter-readings', $renter->id), [
            'readings' => [
                'hot_water' => [
                    'value' => '',
                    'reading_date' => '',
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    $this->assertDatabaseMissing('meter_readings', [
        'user_id' => $renter->id,
        'type' => MeterType::HotWater->value,
        'is_initial' => true,
    ]);
});

test('initial meter readings are hidden from admin meter readings list', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'is_initial' => true,
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'is_initial' => false,
    ]);

    $this->actingAs(adminUser())
        ->get(route('meter-readings.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('meterReadings', 1));
});
