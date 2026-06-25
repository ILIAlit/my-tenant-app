<?php

use App\Enums\ChargeStatus;
use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\PaymentStatus;
use App\Models\Charge;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard includes recent payments limited to five', function () {
    $renter = User::factory()->renter()->create();
    $charge = Charge::factory()->create(['user_id' => $renter->id]);

    foreach (range(1, 6) as $index) {
        Payment::factory()->create([
            'charge_id' => $charge->id,
            'amount' => $index * 10,
            'status' => PaymentStatus::Approved,
            'created_at' => now()->subMinutes(6 - $index),
        ]);
    }

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentPayments', 5)
            ->where('recentPayments.0.amount', 60)
            ->where('recentPayments.4.amount', 20)
            ->where('recentPayments.0.renter.full_name', trim("{$renter->last_name} {$renter->name} {$renter->middle_name}")));
});

test('admin dashboard includes recent meter readings limited to five', function () {
    $renter = User::factory()->renter()->create();
    Room::factory()->create([
        'user_id' => $renter->id,
        'number' => '101',
        'floor' => 1,
    ]);

    foreach (range(1, 6) as $index) {
        MeterReading::factory()->create([
            'user_id' => $renter->id,
            'type' => MeterType::ColdWater,
            'reading_date' => "2026-06-0{$index}",
            'value' => 100 + $index,
            'is_initial' => false,
            'created_at' => now()->subMinutes(6 - $index),
        ]);
    }

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentMeterReadings', 5)
            ->where('recentMeterReadings.0.reading_date', '2026-06-06')
            ->where('recentMeterReadings.0.renter.room.number', '101'));
});

test('recent meter readings exclude initial readings', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'is_initial' => true,
        'reading_date' => '2026-06-01',
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'is_initial' => false,
        'reading_date' => '2026-06-10',
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentMeterReadings', 1)
            ->where('recentMeterReadings.0.reading_date', '2026-06-10'));
});

test('recent records exclude archived charges and meter readings', function () {
    $renter = User::factory()->renter()->create();

    $activeCharge = Charge::factory()->create(['user_id' => $renter->id]);
    $archivedCharge = Charge::factory()->create([
        'user_id' => null,
        'status' => ChargeStatus::Archived,
    ]);

    Payment::factory()->create(['charge_id' => $activeCharge->id]);
    Payment::factory()->create(['charge_id' => $archivedCharge->id]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'is_initial' => false,
        'reading_date' => '2026-06-10',
    ]);

    MeterReading::factory()->create([
        'user_id' => null,
        'status' => MeterReadingStatus::Archived,
        'is_initial' => false,
        'reading_date' => '2026-06-11',
    ]);

    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentPayments', 1)
            ->has('recentMeterReadings', 1)
            ->where('recentPayments.0.charge.id', $activeCharge->id)
            ->where('recentMeterReadings.0.reading_date', '2026-06-10'));
});

test('renter dashboard does not include recent records', function () {
    Payment::factory()->create();
    MeterReading::factory()->create(['is_initial' => false]);

    $this->actingAs(User::factory()->renter()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('recentPayments', null)
            ->where('recentMeterReadings', null)
            ->where('rentersWithDebt', null));
});
