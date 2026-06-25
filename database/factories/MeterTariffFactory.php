<?php

namespace Database\Factories;

use App\Enums\MeterType;
use App\Enums\RoomType;
use App\Models\MeterTariff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeterTariff>
 */
class MeterTariffFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type' => RoomType::Room,
            'type' => fake()->randomElement(MeterType::cases()),
            'price_per_unit' => fake()->randomFloat(4, 0.1, 10),
        ];
    }
}
