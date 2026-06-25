<?php

namespace App\Concerns;

use App\Enums\ChargeStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

trait ChargeValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function chargeRules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::RENTER->value),
            ],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'last_payment_date' => ['nullable', 'date'],
            'status' => ['required', new Enum(ChargeStatus::class)],
        ];
    }
}
