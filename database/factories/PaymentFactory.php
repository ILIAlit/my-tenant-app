<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Charge;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'charge_id' => Charge::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'receipt_path' => 'receipts/'.fake()->uuid().'.jpg',
            'status' => PaymentStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Approved,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Rejected,
        ]);
    }
}
