<?php

use App\Enums\MeterType;
use App\Models\MeterReading;
use App\Models\User;
use App\Services\MeterReadingConsumptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('first reading has no consumption', function () {
    $renter = User::factory()->renter()->create();

    $reading = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 100,
    ]);

    $service = app(MeterReadingConsumptionService::class);
    $result = $service->calculateForReadings(collect([$reading]), collect([$reading]));

    expect($result[$reading->id])->toBe([
        'previous_value' => null,
        'consumption' => null,
    ]);
});

test('consumption is current value minus previous reading for same type', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::Electricity,
        'reading_date' => '2026-05-01',
        'value' => 1000,
    ]);

    $current = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::Electricity,
        'reading_date' => '2026-06-01',
        'value' => 1125.5,
    ]);

    $history = MeterReading::query()->where('user_id', $renter->id)->get();
    $service = app(MeterReadingConsumptionService::class);
    $result = $service->calculateForReadings(collect([$current]), $history);

    expect($result[$current->id])->toBe([
        'previous_value' => 1000.0,
        'consumption' => 125.5,
    ]);
});

test('consumption is calculated separately per meter type', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-05-01',
        'value' => 50,
    ]);

    $current = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 200,
    ]);

    $history = MeterReading::query()->where('user_id', $renter->id)->get();
    $service = app(MeterReadingConsumptionService::class);
    $result = $service->calculateForReadings(collect([$current]), $history);

    expect($result[$current->id])->toBe([
        'previous_value' => null,
        'consumption' => null,
    ]);
});

test('renter page includes consumption using history outside filter', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-05-01',
        'value' => 100,
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-20',
        'value' => 145.25,
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
            ->where('meterReadings.0.previous_value', 100)
            ->where('meterReadings.0.consumption', 45.25));
});

test('admin page includes consumption for readings', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-05-01',
        'value' => 10,
    ]);

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-06-01',
        'value' => 15.5,
    ]);

    $this->actingAs(adminUser())
        ->get(route('meter-readings.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/meter-readings')
            ->has('meterReadings', 2)
            ->where('meterReadings.0.consumption', 5.5)
            ->where('meterReadings.1.consumption', null));
});

test('periodic reading uses initial reading as baseline for consumption', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-01-01',
        'value' => 100,
        'is_initial' => true,
    ]);

    $current = MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::ColdWater,
        'reading_date' => '2026-06-01',
        'value' => 130,
        'is_initial' => false,
    ]);

    $this->actingAs($renter)
        ->get(route('renter.meter-readings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meterReadings', 1)
            ->where('meterReadings.0.previous_value', 100)
            ->where('meterReadings.0.consumption', 30));
});
