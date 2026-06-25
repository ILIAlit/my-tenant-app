<?php

use App\Enums\ChargeStatus;
use App\Enums\RoomPlanDisplayStatus;
use App\Enums\RoomStatus;
use App\Models\Charge;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('free room has free plan display status', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Free]);

    expect($room->planDisplayStatus())->toBe(RoomPlanDisplayStatus::Free);
});

test('room in repair has repair plan display status', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Repair]);

    expect($room->planDisplayStatus())->toBe(RoomPlanDisplayStatus::Repair);
});

test('occupied room without payment issues has occupied plan display status', function () {
    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 500,
        'status' => ChargeStatus::Paid,
    ]);

    $room->load('renter.charges');

    expect($room->planDisplayStatus())->toBe(RoomPlanDisplayStatus::Occupied);
});

test('occupied room with overdue charge has debt plan display status', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-15',
        'status' => ChargeStatus::Debt,
    ]);

    $room->load('renter.charges');

    expect($room->planDisplayStatus())->toBe(RoomPlanDisplayStatus::Debt);
});

test('occupied room with pending payment has awaiting payment plan display status', function () {
    $this->travelTo('2026-06-10');

    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 100,
        'last_payment_date' => '2026-06-20',
        'status' => ChargeStatus::Pending,
    ]);

    $room->load('renter.charges');

    expect($room->planDisplayStatus())->toBe(RoomPlanDisplayStatus::AwaitingPayment);
});

test('occupied room with unpaid charge before due date has awaiting payment plan display status', function () {
    $this->travelTo('2026-06-10');

    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-20',
        'status' => ChargeStatus::Unpaid,
    ]);

    $room->load('renter.charges');

    expect($room->planDisplayStatus())->toBe(RoomPlanDisplayStatus::AwaitingPayment);
});

test('debt takes priority over awaiting payment', function () {
    $this->travelTo('2026-06-20');

    $renter = User::factory()->renter()->create();
    $room = Room::factory()->create([
        'user_id' => $renter->id,
        'status' => RoomStatus::Occupied,
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 500,
        'paid_amount' => 0,
        'last_payment_date' => '2026-06-15',
        'status' => ChargeStatus::Debt,
    ]);

    Charge::factory()->create([
        'user_id' => $renter->id,
        'total_amount' => 300,
        'paid_amount' => 100,
        'last_payment_date' => '2026-06-25',
        'status' => ChargeStatus::Pending,
    ]);

    $room->load('renter.charges');

    expect($room->planDisplayStatus())->toBe(RoomPlanDisplayStatus::Debt);
});

test('admin dashboard includes house plan grouped by floors', function () {
    Room::factory()->create([
        'number' => '101',
        'floor' => 1,
        'status' => RoomStatus::Free,
    ]);

    Room::factory()->create([
        'number' => '201',
        'floor' => 2,
        'status' => RoomStatus::Repair,
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('housePlan.floors', 2)
            ->has('housePlan.rooms', 2)
            ->where('housePlan.floors.0', 1)
            ->where('housePlan.floors.1', 2)
            ->where('housePlan.rooms.0.display_status', 'free')
            ->where('housePlan.rooms.1.display_status', 'repair'));
});

test('renter dashboard does not include house plan', function () {
    Room::factory()->create();

    $this->actingAs(User::factory()->renter()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('housePlan', null)
            ->where('recentPayments', null)
            ->where('recentMeterReadings', null)
            ->where('rentersWithDebt', null));
});
