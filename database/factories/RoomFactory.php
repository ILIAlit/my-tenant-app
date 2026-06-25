<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => RoomType::Room,
            'number' => fake()->unique()->bothify('?##'),
            'floor' => fake()->numberBetween(1, 10),
            'area' => fake()->randomFloat(2, 15, 120),
            'status' => fake()->randomElement(RoomStatus::cases()),
            'last_repair_date' => fake()->optional()->date(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function garage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => RoomType::Garage,
            'number' => fake()->unique()->numerify('G-###'),
            'floor' => null,
        ]);
    }
}
