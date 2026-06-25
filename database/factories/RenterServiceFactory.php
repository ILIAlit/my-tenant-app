<?php

namespace Database\Factories;

use App\Models\RenterService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RenterService>
 */
class RenterServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->renter(),
            'name' => fake()->words(2, true),
            'price' => fake()->randomFloat(2, 5, 200),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
