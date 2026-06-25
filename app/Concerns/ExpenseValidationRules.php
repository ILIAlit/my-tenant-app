<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

trait ExpenseValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function expenseRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
