<?php

namespace Database\Factories;

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Charge>
 */
class ChargeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = fake()->randomFloat(2, 50, 1000);
        $status = fake()->randomElement(array_filter(
            ChargeStatus::cases(),
            fn (ChargeStatus $status): bool => $status !== ChargeStatus::Archived,
        ));

        $paidAmount = match ($status) {
            ChargeStatus::Paid => $totalAmount,
            ChargeStatus::Unpaid => fake()->randomFloat(2, 0, $totalAmount * 0.5),
            ChargeStatus::Debt => fake()->randomFloat(2, 0, $totalAmount * 0.5),
            ChargeStatus::Pending => fake()->randomFloat(2, 0, $totalAmount),
            ChargeStatus::Archived => $totalAmount,
        };

        return [
            'user_id' => User::factory()->renter(),
            'category' => ChargeCategory::Other,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'last_payment_date' => fake()->optional()->date(),
            'status' => $status,
        ];
    }
}
