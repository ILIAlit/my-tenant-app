<?php

namespace Database\Factories;

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeterReading>
 */
class MeterReadingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->renter(),
            'type' => fake()->randomElement(MeterType::metered()),
            'reading_date' => fake()->date(),
            'value' => fake()->randomFloat(3, 1, 99999),
            'is_initial' => false,
            'status' => MeterReadingStatus::Approved,
        ];
    }
}
