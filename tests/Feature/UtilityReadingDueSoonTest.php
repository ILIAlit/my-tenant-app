<?php

use App\Enums\UserRole;
use App\Enums\UtilityReadingStatus;
use App\Models\Contracts;
use App\Models\Rooms;
use App\Models\User;
use App\Models\UtilityReading;
use Carbon\CarbonImmutable;

beforeEach(function () {
    // За 3 дня до конца периода (период заканчивается 15.06.2026).
    CarbonImmutable::setTestNow('2026-06-12');

    $this->renter = User::factory()->create([
        'login' => 'renter1',
        'role' => UserRole::RENTER->value,
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

test('renter is reminded three days before the period ends', function () {
    $this->artisan('utility-readings:notify-due-soon')->assertSuccessful();

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('utility_reading_due_soon')
        ->and($notification->data['days_left'])->toBe(3)
        ->and($notification->data['room_number'])->toBe(101)
        ->and($notification->data['period_start'])->toBe('2026-05-15');
});

test('no reminder when readings already submitted for the period', function () {
    UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-05-15',
        'period_end' => '2026-06-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Review,
    ]);

    $this->artisan('utility-readings:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(0);
});

test('rejected readings still trigger a reminder', function () {
    UtilityReading::create([
        'rooms_id' => $this->room->id,
        'contracts_id' => $this->contract->id,
        'period_start' => '2026-05-15',
        'period_end' => '2026-06-15',
        'cold_water' => 10,
        'submitted_by' => $this->renter->id,
        'status' => UtilityReadingStatus::Rejected,
    ]);

    $this->artisan('utility-readings:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(1);
});

test('no reminder when the period does not end in three days', function () {
    CarbonImmutable::setTestNow('2026-06-10');

    $this->artisan('utility-readings:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(0);
});

test('reminder is not sent twice for the same period', function () {
    $this->artisan('utility-readings:notify-due-soon')->assertSuccessful();
    $this->artisan('utility-readings:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(1);
});

test('custom days option changes the reminder window', function () {
    CarbonImmutable::setTestNow('2026-06-10');

    $this->artisan('utility-readings:notify-due-soon', ['--days' => 5])->assertSuccessful();

    $notification = $this->renter->fresh()->notifications()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['days_left'])->toBe(5);
});

test('rooms without a tenant are skipped', function () {
    $this->room->update(['user_id' => null]);

    $this->artisan('utility-readings:notify-due-soon')->assertSuccessful();

    expect($this->renter->fresh()->notifications()->count())->toBe(0);
});
