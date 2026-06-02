<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait NewsValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function newsCreateRules(): array
    {
        return [
            'title' => $this->textRules(),
            'text' => $this->textRules(),
            'date' => $this->dateRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function textRules(): array
    {
        return ['required', 'string'];
    }

    /**
     * Get the validation rules used to validate user fio.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function dateRules(): array
    {
        return ['required', 'date'];
    }
}
