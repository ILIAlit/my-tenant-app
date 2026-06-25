<?php

use App\Enums\MeterType;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function meterReadingPayload(array $overrides = []): array
{
    return array_merge([
        'user_id' => User::factory()->renter()->create()->id,
        'type' => MeterType::ColdWater->value,
        'reading_date' => '2026-06-01',
        'value' => 123.456,
    ], $overrides);
}

test('guest cannot access meter readings page', function () {
    $this->get(route('meter-readings.get'))->assertRedirect(route('login'));
});

test('renter cannot access admin meter readings page', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->get(route('meter-readings.get'))
        ->assertForbidden();
});

test('admin can view meter readings page', function () {
    MeterReading::factory()->create();

    $this->actingAs(adminUser())
        ->get(route('meter-readings.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/meter-readings')
            ->has('meterReadings', 1)
            ->has('renters'));
});

test('admin can create meter reading', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->post(route('meter-readings.create'), meterReadingPayload([
            'user_id' => $renter->id,
            'type' => MeterType::Electricity->value,
            'value' => 500.5,
        ]))
        ->assertRedirect(route('meter-readings.get'));

    $this->assertDatabaseHas('meter_readings', [
        'user_id' => $renter->id,
        'type' => MeterType::Electricity->value,
        'value' => 500.5,
    ]);
});

test('admin cannot create duplicate meter reading for same renter type and date', function () {
    $renter = User::factory()->renter()->create();

    MeterReading::factory()->create([
        'user_id' => $renter->id,
        'type' => MeterType::HotWater,
        'reading_date' => '2026-06-01',
    ]);

    $this->actingAs(adminUser())
        ->post(route('meter-readings.create'), meterReadingPayload([
            'user_id' => $renter->id,
            'type' => MeterType::HotWater->value,
            'reading_date' => '2026-06-01',
        ]))
        ->assertSessionHasErrors('type');
});

test('meter reading user must be renter', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs(adminUser())
        ->post(route('meter-readings.create'), meterReadingPayload([
            'user_id' => $admin->id,
        ]))
        ->assertSessionHasErrors('user_id');
});

test('admin can update meter reading', function () {
    $reading = MeterReading::factory()->create([
        'type' => MeterType::ColdWater,
        'value' => 100,
        'reading_date' => '2026-06-01',
    ]);

    $this->actingAs(adminUser())
        ->put(route('meter-readings.update', $reading->id), [
            'user_id' => $reading->user_id,
            'type' => MeterType::ColdWater->value,
            'reading_date' => '2026-06-15',
            'value' => 150.25,
        ])
        ->assertRedirect(route('meter-readings.get'));

    expect((float) $reading->fresh()->value)->toBe(150.25);
    expect($reading->fresh()->reading_date?->format('Y-m-d'))->toBe('2026-06-15');
});

test('admin can delete meter reading', function () {
    $reading = MeterReading::factory()->create();

    $this->actingAs(adminUser())
        ->delete(route('meter-readings.destroy', $reading->id))
        ->assertRedirect(route('meter-readings.get'));

    $this->assertDatabaseMissing('meter_readings', ['id' => $reading->id]);
});

test('meter reading requires valid type', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs(adminUser())
        ->post(route('meter-readings.create'), meterReadingPayload([
            'user_id' => $renter->id,
            'type' => 'invalid',
        ]))
        ->assertSessionHasErrors('type');
});
