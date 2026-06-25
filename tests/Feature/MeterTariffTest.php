<?php

use App\Enums\MeterType;
use App\Enums\RoomType;
use App\Models\MeterTariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view tariffs on meter readings page', function () {
    $this->actingAs(adminUser())
        ->get(route('meter-readings.get'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('tariffs.room')
            ->has('tariffs.garage')
            ->where('tariffs.room.cold_water', 0)
            ->where('tariffs.room.hot_water', 0)
            ->where('tariffs.room.electricity', 0)
            ->where('tariffs.room.sewage', 0)
            ->where('tariffs.garage.cold_water', 0)
            ->where('tariffs.garage.hot_water', 0)
            ->where('tariffs.garage.electricity', 0)
            ->where('tariffs.garage.sewage', 0));
});

test('admin can update meter tariffs for rooms and garages separately', function () {
    $this->actingAs(adminUser())
        ->put(route('meter-readings.tariffs.update'), [
            'tariffs' => [
                'room' => [
                    'cold_water' => 1.25,
                    'hot_water' => 3.5,
                    'electricity' => 0.28,
                    'sewage' => 8,
                ],
                'garage' => [
                    'cold_water' => 0.75,
                    'hot_water' => 2.1,
                    'electricity' => 0.15,
                    'sewage' => 4,
                ],
            ],
        ])
        ->assertRedirect(route('meter-readings.get'));

    expect((float) MeterTariff::query()->where('room_type', RoomType::Room)->where('type', MeterType::ColdWater)->value('price_per_unit'))->toBe(1.25);
    expect((float) MeterTariff::query()->where('room_type', RoomType::Room)->where('type', MeterType::HotWater)->value('price_per_unit'))->toBe(3.5);
    expect((float) MeterTariff::query()->where('room_type', RoomType::Room)->where('type', MeterType::Electricity)->value('price_per_unit'))->toBe(0.28);
    expect((float) MeterTariff::query()->where('room_type', RoomType::Room)->where('type', MeterType::Sewage)->value('price_per_unit'))->toBe(8.0);

    expect((float) MeterTariff::query()->where('room_type', RoomType::Garage)->where('type', MeterType::ColdWater)->value('price_per_unit'))->toBe(0.75);
    expect((float) MeterTariff::query()->where('room_type', RoomType::Garage)->where('type', MeterType::HotWater)->value('price_per_unit'))->toBe(2.1);
    expect((float) MeterTariff::query()->where('room_type', RoomType::Garage)->where('type', MeterType::Electricity)->value('price_per_unit'))->toBe(0.15);
    expect((float) MeterTariff::query()->where('room_type', RoomType::Garage)->where('type', MeterType::Sewage)->value('price_per_unit'))->toBe(4.0);
});

test('renter cannot update meter tariffs', function () {
    $renter = User::factory()->renter()->create();

    $this->actingAs($renter)
        ->put(route('meter-readings.tariffs.update'), [
            'tariffs' => [
                'room' => [
                    'cold_water' => 1,
                    'hot_water' => 1,
                    'electricity' => 1,
                    'sewage' => 1,
                ],
                'garage' => [
                    'cold_water' => 1,
                    'hot_water' => 1,
                    'electricity' => 1,
                    'sewage' => 1,
                ],
            ],
        ])
        ->assertForbidden();
});
