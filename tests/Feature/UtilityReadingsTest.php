<?php

use App\Enums\UserRole;
use App\Enums\UtilityReadingStatus;
use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\Rooms;
use App\Models\User;
use App\Models\UtilityReading;
use App\Models\UtilityTariff;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-06-13');

    $this->renter = User::factory()->create([
        'login' => 'renter1',
        'role' => UserRole::RENTER->value,
    ]);

    $this->admin = User::factory()->create([
        'login' => 'admin1',
        'role' => UserRole::ADMIN->value,
    ]);

    $this->room = Rooms::create([
        'user_id' => $this->renter->id,
        'number' => 101,
        'floor' => 1,
        'square' => 20,
        'status' => 'used',
    ]);

    $this->contract = Contracts::create([
        'rooms_id' => $this->room->id,
        'number' => 'Д-101',
        'conclusion_date' => '2026-01-15',
        'expiration_date' => '2027-01-15',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('renter can view utility readings page', function () {
    $this->actingAs($this->renter)
        ->get(route('utility-readings.get'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('utility-readings/utility-readings')
            ->has('roomsUtilityData'));
});

test('renter can view utility data on rent rooms page', function () {
    $this->actingAs($this->renter)
        ->get(route('rooms.get-renter-rooms'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('rent/rooms')
            ->has('rooms', 1)
            ->missing('roomsUtilityData'));
});

test('renter can submit utility readings for a completed billing period', function () {
    $response = $this->actingAs($this->renter)
        ->post(route('utility-readings.create'), [
            'rooms_id' => $this->room->id,
            'contracts_id' => $this->contract->id,
            'period_start' => '2026-04-15',
            'cold_water' => 12.5,
            'hot_water' => 8.25,
            'electricity' => 150,
        ]);

    $response->assertRedirect();

    $reading = UtilityReading::first();

    expect($reading)->not->toBeNull()
        ->and($reading->rooms_id)->toBe($this->room->id)
        ->and($reading->contracts_id)->toBe($this->contract->id)
        ->and($reading->period_start->format('Y-m-d'))->toBe('2026-04-15')
        ->and($reading->period_end->format('Y-m-d'))->toBe('2026-05-15')
        ->and((float) $reading->cold_water)->toBe(12.5)
        ->and((float) $reading->hot_water)->toBe(8.25)
        ->and((float) $reading->electricity)->toBe(150.0)
        ->and($reading->submitted_by)->toBe($this->renter->id)
        ->and($reading->status)->toBe(UtilityReadingStatus::Review);
});

test('renter can submit utility readings with meter photos', function () {
    Storage::fake('public');

    $response = $this->actingAs($this->renter)
        ->post(route('utility-readings.create'), [
            'rooms_id' => $this->room->id,
            'contracts_id' => $this->contract->id,
            'period_start' => '2026-03-15',
            'cold_water' => 10,
            'cold_water_photo' => UploadedFile::fake()->create('cold.jpg', 100, 'image/jpeg'),
            'electricity' => 100,
            'electricity_photo' => UploadedFile::fake()->create('electric.jpg', 100, 'image/jpeg'),
        ]);

    $response->assertRedirect();

    $reading = UtilityReading::first();

    expect($reading)->not->toBeNull()
        ->and($reading->cold_water_photo_path)->not->toBeNull()
        ->and($reading->electricity_photo_path)->not->toBeNull()
        ->and($reading->hot_water_photo_path)->toBeNull();

    Storage::disk('public')->assertExists($reading->cold_water_photo_path);
    Storage::disk('public')->assertExists($reading->electricity_photo_path);
});

test('renter cannot submit readings before billing period starts', function () {
    $this->actingAs($this->renter)
        ->post(route('utility-readings.create'), [
            'rooms_id' => $this->room->id,
            'contracts_id' => $this->contract->id,
            'period_start' => '2026-07-15',
            'cold_water' => 1,
        ])
        ->assertSessionHasErrors('period_start');

    $this->assertDatabaseCount('utility_readings', 0);
});

test('renter can submit readings for current billing period', function () {
    CarbonImmutable::setTestNow('2026-06-13');

    $shortContract = Contracts::create([
        'rooms_id' => $this->room->id,
        'number' => 'Д-short',
        'conclusion_date' => '2026-06-11',
        'expiration_date' => '2026-06-28',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);

    $this->actingAs($this->renter)
        ->post(route('utility-readings.create'), [
            'rooms_id' => $this->room->id,
            'contracts_id' => $shortContract->id,
            'period_start' => '2026-06-11',
            'cold_water' => 5,
        ])
        ->assertRedirect();

    $reading = UtilityReading::where('contracts_id', $shortContract->id)->first();

    expect($reading)->not->toBeNull()
        ->and($reading->period_start->format('Y-m-d'))->toBe('2026-06-11')
        ->and($reading->period_end->format('Y-m-d'))->toBe('2026-07-11')
        ->and((float) $reading->cold_water)->toBe(5.0);
});

test('renter cannot submit duplicate readings for the same period', function () {
    UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-14',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Approved,
    ]);

    $this->actingAs($this->renter)
        ->post(route('utility-readings.create'), [
            'rooms_id' => $this->room->id,
            'contracts_id' => $this->contract->id,
            'period_start' => '2026-04-15',
            'cold_water' => 11,
        ])
        ->assertSessionHasErrors('period_start');
});

test('admin can update utility readings', function () {
    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-14',
        'cold_water' => 10,
        'hot_water' => 5,
        'electricity' => 100,
        'submitted_by' => $this->renter->id,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.update', ['id' => $reading->id]), [
            'cold_water' => 11.5,
            'hot_water' => 6,
            'electricity' => 120,
        ])
        ->assertRedirect(route('utility-readings.all-get'));

    $this->assertDatabaseHas('utility_readings', [
        'id' => $reading->id,
        'cold_water' => 11.5,
        'hot_water' => 6,
        'electricity' => 120,
    ]);
});

test('admin can view all utility readings page', function () {
    UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-14',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Approved,
    ]);

    $this->actingAs($this->admin)
        ->get(route('utility-readings.all-get'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/utility-readings')
            ->has('readings', 1));
});

test('admin cannot submit utility readings', function () {
    $this->actingAs($this->admin)
        ->post(route('utility-readings.create'), [
            'rooms_id' => $this->room->id,
            'contracts_id' => $this->contract->id,
            'period_start' => '2026-04-15',
            'cold_water' => 10,
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('utility_readings', 0);
});

test('admin cannot access renter utility readings page', function () {
    $this->actingAs($this->admin)
        ->get(route('utility-readings.get'))
        ->assertForbidden();
});

test('contract generates monthly billing periods from conclusion date', function () {
    $periods = $this->contract->billingPeriods('2026-05-14');

    expect($periods)->toHaveCount(4)
        ->and($periods[0]['start']->format('Y-m-d'))->toBe('2026-01-15')
        ->and($periods[0]['end']->format('Y-m-d'))->toBe('2026-02-15')
        ->and($periods[3]['start']->format('Y-m-d'))->toBe('2026-04-15')
        ->and($periods[3]['end']->format('Y-m-d'))->toBe('2026-05-15');
});

test('short contract still uses full monthly period end', function () {
    $shortContract = Contracts::create([
        'rooms_id' => $this->room->id,
        'number' => 'Д-short',
        'conclusion_date' => '2026-06-11',
        'expiration_date' => '2026-06-28',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);

    $periods = $shortContract->billingPeriods();

    expect($periods)->toHaveCount(1)
        ->and($periods[0]['start']->format('Y-m-d'))->toBe('2026-06-11')
        ->and($periods[0]['end']->format('Y-m-d'))->toBe('2026-07-11');
});

test('admin can update utility tariffs', function () {
    $this->actingAs($this->admin)
        ->post(route('utility-tariffs.update'), [
            'cold_water_rate' => 45.5,
            'hot_water_rate' => 210,
            'electricity_rate' => 6.25,
        ])
        ->assertRedirect(route('utility-readings.all-get'));

    $tariff = UtilityTariff::current();

    expect((float) $tariff->cold_water_rate)->toBe(45.5)
        ->and((float) $tariff->hot_water_rate)->toBe(210.0)
        ->and((float) $tariff->electricity_rate)->toBe(6.25);
});

test('approving utility reading without tariff does not create an invoice', function () {
    UtilityTariff::current()->update([
        'cold_water_rate' => 0,
        'hot_water_rate' => 0,
        'electricity_rate' => 0,
    ]);

    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 10,
        'hot_water' => 5,
        'electricity' => 100,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.approve', ['id' => $reading->id]))
        ->assertRedirect(route('utility-readings.all-get'));

    $reading->refresh();

    expect($reading->status)->toBe(UtilityReadingStatus::Approved)
        ->and($reading->utility_amount)->toBe(0)
        ->and($reading->invoices_id)->toBeNull()
        ->and(Invoices::count())->toBe(0);
});

test('approving first utility reading creates a separate utility invoice', function () {
    UtilityTariff::current()->update([
        'cold_water_rate' => 50,
        'hot_water_rate' => 100,
        'electricity_rate' => 5,
    ]);

    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 12.5,
        'hot_water' => 6,
        'electricity' => 150,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.approve', ['id' => $reading->id]))
        ->assertRedirect(route('utility-readings.all-get'));

    $reading->refresh();

    // cold: 12.5 * 50 = 625, hot: 6 * 100 = 600, electricity: 150 * 5 = 750 => 1975
    expect($reading->utility_amount)->toBe(1975)
        ->and((float) $reading->cold_water_consumption)->toBe(12.5)
        ->and((float) $reading->hot_water_consumption)->toBe(6.0)
        ->and((float) $reading->electricity_consumption)->toBe(150.0);

    $invoice = Invoices::find($reading->invoices_id);

    expect($invoice)->not->toBeNull()
        ->and($invoice->total_price)->toBe(1975)
        ->and($invoice->user_id)->toBe($this->renter->id)
        ->and($invoice->rooms_id)->toBe($this->room->id)
        ->and($invoice->name)->toContain('Коммунальные услуги')
        ->and($invoice->create_date)->toBe('15.04.2026')
        ->and($invoice->due_date)->toBe('15.05.2026');
});

test('utility invoice is separate from the rent invoice for the same period', function () {
    UtilityTariff::current()->update([
        'cold_water_rate' => 50,
        'hot_water_rate' => 0,
        'electricity_rate' => 0,
    ]);

    $rentInvoice = Invoices::create([
        'user_id' => $this->renter->id,
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'name' => 'Начисление за комнату № 101',
        'total_price' => 15000,
        'create_date' => '15.04.2026',
        'due_date' => '15.05.2026',
    ]);

    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 12,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.approve', ['id' => $reading->id]))
        ->assertRedirect(route('utility-readings.all-get'));

    expect($rentInvoice->fresh()->total_price)->toBe(15000)
        ->and(Invoices::count())->toBe(2);

    $utilityInvoice = Invoices::find($reading->fresh()->invoices_id);

    expect($utilityInvoice)->not->toBeNull()
        ->and($utilityInvoice->id)->not->toBe($rentInvoice->id)
        ->and($utilityInvoice->total_price)->toBe(600)
        ->and($utilityInvoice->name)->toContain('Коммунальные услуги');
});

test('admin can approve utility readings', function () {
    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.approve', ['id' => $reading->id]))
        ->assertRedirect(route('utility-readings.all-get'));

    expect($reading->fresh()->status)->toBe(UtilityReadingStatus::Approved);
});

test('admin can reject utility readings with reason', function () {
    $reading = UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->actingAs($this->admin)
        ->put(route('utility-readings.reject', ['id' => $reading->id]), [
            'rejection_reason' => 'Фото нечитаемо',
        ])
        ->assertRedirect(route('utility-readings.all-get'));

    $reading->refresh();

    expect($reading->status)->toBe(UtilityReadingStatus::Rejected)
        ->and($reading->rejection_reason)->toBe('Фото нечитаемо');
});

test('renter can resubmit readings after rejection', function () {
    UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Rejected,
        'rejection_reason' => 'Неверные показания',
    ]);

    $this->actingAs($this->renter)
        ->post(route('utility-readings.create'), [
            'rooms_id' => $this->room->id,
            'contracts_id' => $this->contract->id,
            'period_start' => '2026-04-15',
            'cold_water' => 12,
        ])
        ->assertRedirect();

    expect(UtilityReading::query()->where('rooms_id', $this->room->id)->count())->toBe(1);

    $reading = UtilityReading::first();

    expect($reading->status)->toBe(UtilityReadingStatus::Review)
        ->and((float) $reading->cold_water)->toBe(12.0)
        ->and($reading->rejection_reason)->toBeNull();
});

test('rejected period becomes available again for renter', function () {
    UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-04-15',
        'period_end' => '2026-05-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Rejected,
    ]);

    $response = $this->actingAs($this->renter)
        ->get(route('utility-readings.get'));

    $response->assertSuccessful();

    $availablePeriods = $response->original->getData()['page']['props']['roomsUtilityData'][0]['availablePeriods'];

    expect(collect($availablePeriods)->pluck('period_start')->all())->toContain('2026-04-15');
});

test('contract with early expiration still shows monthly period in form', function () {
    $contract = Contracts::create([
        'rooms_id' => $this->room->id,
        'number' => 'Д-2041',
        'conclusion_date' => '2026-06-03',
        'expiration_date' => '2026-06-28',
        'payment_terms' => 'Ежемесячно',
        'termination_terms' => 'За месяц',
        'file_path' => null,
    ]);

    $periods = $contract->billingPeriods();

    expect($periods)->toHaveCount(1)
        ->and($periods[0]['start']->format('d.m.Y'))->toBe('03.06.2026')
        ->and($periods[0]['end']->format('d.m.Y'))->toBe('03.07.2026');
});
