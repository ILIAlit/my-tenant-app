<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->renter(),
            'number' => fake()->unique()->numerify('ДГ-####'),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->date(),
            'monthly_rent' => fake()->randomFloat(2, 100, 2000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
