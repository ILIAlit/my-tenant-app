<?php

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\RoomStatus;
use App\Models\Charge;
use App\Models\MeterReading;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('changing renter room archives charges and meter readings without renter link', function () {
    $renter = User::factory()->renter()->create();
    $oldRoom = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);
    $newRoom = Room::factory()->create(['status' => RoomStatus::Free]);

    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'category' => ChargeCategory::Rent,
        'status' => ChargeStatus::Debt,
    ]);

    $initialReading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'is_initial' => true,
        'status' => MeterReadingStatus::Approved,
    ]);

    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::Electricity,
        'status' => MeterReadingStatus::Approved,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $newRoom->id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renters.settings', $renter->id));

    expect($charge->fresh()->status)->toBe(ChargeStatus::Archived);
    expect($charge->fresh()->user_id)->toBeNull();
    expect($charge->fresh()->archived_renter_name)->toBe(trim("{$renter->last_name} {$renter->name} {$renter->middle_name}"));
    expect($charge->fresh()->archived_room_label)->toContain((string) $oldRoom->number);
    expect($initialReading->fresh()->status)->toBe(MeterReadingStatus::Archived);
    expect($initialReading->fresh()->user_id)->toBeNull();
    expect($reading->fresh()->status)->toBe(MeterReadingStatus::Archived);
    expect($reading->fresh()->user_id)->toBeNull();

    expect(Charge::query()->where('user_id', $renter->id)->count())->toBe(0);
    expect(MeterReading::query()->where('user_id', $renter->id)->count())->toBe(0);
});

test('changing garage to room archives financial history', function () {
    $renter = User::factory()->renter()->create();
    $garage = Room::factory()->garage()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);
    $room = Room::factory()->create(['status' => RoomStatus::Free]);

    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'status' => ChargeStatus::Unpaid,
    ]);

    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $room->id,
        ])
        ->assertSessionHasNoErrors();

    expect($garage->fresh()->user_id)->toBeNull();
    expect($room->fresh()->user_id)->toBe($renter->id);
    expect($charge->fresh()->status)->toBe(ChargeStatus::Archived);
    expect($charge->fresh()->user_id)->toBeNull();
    expect($reading->fresh()->status)->toBe(MeterReadingStatus::Archived);
    expect($reading->fresh()->user_id)->toBeNull();
});

test('removing renter from room archives charges and meter readings', function () {
    $renter = User::factory()->renter()->create();
    Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'status' => ChargeStatus::Paid,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => null,
        ])
        ->assertSessionHasNoErrors();

    expect($charge->fresh()->status)->toBe(ChargeStatus::Archived);
    expect($charge->fresh()->user_id)->toBeNull();
});

test('first room assignment does not archive existing charges', function () {
    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create(['status' => RoomStatus::Free]);

    $charge = Charge::factory()->create([
        'user_id' => $renter->id,
        'status' => ChargeStatus::Unpaid,
    ]);

    $this->actingAs(adminUser())
        ->put(route('renters.assign-room', $renter->id), [
            'room_id' => $room->id,
        ])
        ->assertSessionHasNoErrors();

    expect($charge->fresh()->status)->toBe(ChargeStatus::Unpaid);
    expect($charge->fresh()->user_id)->toBe($renter->id);
});

test('archived charges are hidden from admin charges page', function () {
    $renter = User::factory()->renter()->create();

    Charge::factory()->create([
        'user_id' => $renter->id,
        'status' => ChargeStatus::Unpaid,
    ]);

    Charge::factory()->create([
        'user_id' => null,
        'status' => ChargeStatus::Archived,
        'archived_renter_name' => 'Иванов Иван',
        'archived_room_label' => 'Комната 101 (эт. 2)',
    ]);

    $this->actingAs(adminUser())
        ->get(route('charges.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('charges', 1)
            ->where('charges.0.status', ChargeStatus::Unpaid->value)
            ->where('showArchive', false));

    $this->actingAs(adminUser())
        ->get(route('charges.get', ['archive' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('charges', 1)
            ->where('charges.0.status', ChargeStatus::Archived->value)
            ->where('charges.0.renter.full_name', 'Иванов Иван')
            ->where('charges.0.renter.room_label', 'Комната 101 (эт. 2)')
            ->where('showArchive', true));
});

test('archived meter readings are hidden from admin meter readings page', function () {
    MeterReading::factory()->create([
        'user_id' => User::factory()->renter(),
        'status' => MeterReadingStatus::Approved,
    ]);

    MeterReading::factory()->create([
        'user_id' => null,
        'status' => MeterReadingStatus::Archived,
        'archived_renter_name' => 'Петров Пётр',
        'archived_room_label' => 'Гараж 5',
        'consumption' => 12.5,
        'charged_amount' => 45.00,
    ]);

    $this->actingAs(adminUser())
        ->get(route('meter-readings.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meterReadings', 1)
            ->where('showArchive', false));

    $this->actingAs(adminUser())
        ->get(route('meter-readings.get', ['archive' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meterReadings', 1)
            ->where('meterReadings.0.status', MeterReadingStatus::Archived->value)
            ->where('meterReadings.0.renter.full_name', 'Петров Пётр')
            ->where('meterReadings.0.renter.room_label', 'Гараж 5')
            ->where('meterReadings.0.consumption', 12.5)
            ->where('meterReadings.0.estimated_cost', 45)
            ->where('showArchive', true));
});
