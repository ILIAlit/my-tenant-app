<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->fioRules(),
            'email' => $this->emailRules($userId),
            'login' => $this->nameRules(),
            'last_name' => $this->fioRules(),
            'middle_name' => $this->fioRules(),
            'phone' => $this->phoneRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user fio.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function fioRules(): array
    {
        return ['string', 'max:255'];
    }

    protected function phoneRules(): array
    {
        return [
            'string',
            'regex:/^\+375(25|29|33|44)\d{7}$/'
        ];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
