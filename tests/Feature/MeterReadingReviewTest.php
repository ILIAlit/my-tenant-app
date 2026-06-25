<?php

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('renter submission creates pending meter reading', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->post(route('renter.meter-readings.store'), [
            'type' => MeterType::ColdWater->value,
            'reading_date' => '2026-06-10',
            'value' => 88.123,
        ])
        ->assertRedirect(route('renter.meter-readings'));

    $this->assertDatabaseHas('meter_readings', [
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater->value,
        'status' => MeterReadingStatus::Pending->value,
    ]);
});

test('admin can approve pending meter reading', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();
    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $reading->id))
        ->assertRedirect(route('meter-readings.get'));

    expect($reading->fresh()->status)->toBe(MeterReadingStatus::Approved);
});

test('admin can reject pending meter reading', function () {
    Notification::fake();

    $renter = User::factory()->renter()->create();
    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'status' => MeterReadingStatus::Pending,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.reject', $reading->id))
        ->assertRedirect(route('meter-readings.get'));

    expect($reading->fresh()->status)->toBe(MeterReadingStatus::Rejected);
});

test('admin cannot approve already processed meter reading', function () {
    $reading = MeterReading::factory()->create([
        'status' => MeterReadingStatus::Approved,
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.approve', $reading->id))
        ->assertSessionHasErrors('id');
});

test('approved readings are used for consumption rejected are not', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-01-01',
        'value' => 100,
        'is_initial' => true,
        'status' => MeterReadingStatus::Approved,
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 130,
        'status' => MeterReadingStatus::Approved,
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-07-01',
        'value' => 150,
        'status' => MeterReadingStatus::Rejected,
    ]);

    $this->actingAs($renter)
        ->get(route('renter.meter-readings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meterReadings', 2)
            ->where('meterReadings.0.status', MeterReadingStatus::Rejected->value)
            ->where('meterReadings.0.consumption', null)
            ->where('meterReadings.1.status', MeterReadingStatus::Approved->value)
            ->where('meterReadings.1.consumption', 30));
});

test('renter can resubmit after rejection for same type and date', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-06-10',
        'status' => MeterReadingStatus::Rejected,
    ]);

    $this->actingAs($renter)
        ->post(route('renter.meter-readings.store'), [
            'type' => MeterType::HotWater->value,
            'reading_date' => '2026-06-10',
            'value' => 75,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('renter.meter-readings'));

    $this->assertDatabaseHas('meter_readings', [
        'user_id' => $renter->id,
        'type' => MeterType::HotWater->value,
        'status' => MeterReadingStatus::Pending->value,
        'value' => 75,
    ]);
});
