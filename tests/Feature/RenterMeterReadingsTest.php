<?php

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access renter meter readings page', function () {
    $this->get(route('renter.meter-readings'))->assertRedirect(route('login'));
});

test('admin cannot access renter meter readings page', function () {
    $this->actingAs(adminUser())
        ->get(route('renter.meter-readings'))
        ->assertForbidden();
});

test('renter can view own meter readings', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::Electricity,
        'value' => 250.5,
        'reading_date' => '2026-06-01',
    ]);

    $this->actingAs($renter)
        ->get(route('renter.meter-readings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('renter/meter-readings')
            ->has('meterReadings', 1)
            ->where('meterReadings.0.type', MeterType::Electricity->value)
            ->where('meterReadings.0.value', 250.5)
            ->where('meterReadings.0.reading_date', '2026-06-01')
            ->where('meterReadings.0.status', MeterReadingStatus::Approved->value)
            ->where('meterReadings.0.previous_value', null)
            ->where('meterReadings.0.consumption', null));
});

test('renter only sees own meter readings', function () {
    $renter = User::factory()->renter()->create();
    $otherRenter = User::factory()->renter()->create();

    MeterReading::factory()->create(['user_id' => $renter->id, 'value' => 100]);
    MeterReading::factory()->create(['user_id' => $otherRenter->id, 'value' => 999]);

    $this->actingAs($renter)
        ->get(route('renter.meter-readings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meterReadings', 1)
            ->where('meterReadings.0.value', 100));
});

test('renter can submit meter reading', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->post(route('renter.meter-readings.store'), [
            'type' => MeterType::HotWater->value,
            'reading_date' => '2026-06-10',
            'value' => 88.123,
        ])
        ->assertRedirect(route('renter.meter-readings'));

    $this->assertDatabaseHas('meter_readings', [
        'user_id' => $renter->id,
        'type' => MeterType::HotWater->value,
        'value' => 88.123,
    ]);
});

test('renter cannot submit duplicate meter reading for same type and date', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-10',
    ]);

    $this->actingAs($renter)
        ->post(route('renter.meter-readings.store'), [
            'type' => MeterType::ColdWater->value,
            'reading_date' => '2026-06-10',
            'value' => 50,
        ])
        ->assertSessionHasErrors('type');
});

test('renter can filter meter readings by date and type', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-05-01',
        'value' => 100,
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-06-15',
        'value' => 200,
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-20',
        'value' => 300,
    ]);

    $this->actingAs($renter)
        ->get(route('renter.meter-readings', [
            'reading_from' => '2026-06-01',
            'reading_to' => '2026-06-30',
            'type' => MeterType::ColdWater->value,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meterReadings', 1)
            ->where('meterReadings.0.value', 300)
            ->where('filters.reading_from', '2026-06-01')
            ->where('filters.reading_to', '2026-06-30')
            ->where('filters.type', MeterType::ColdWater->value));
});

test('reading_to must be after or equal to reading_from', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('renter.meter-readings', [
            'reading_from' => '2026-06-15',
            'reading_to' => '2026-06-01',
        ]))
        ->assertSessionHasErrors('reading_to');
});
