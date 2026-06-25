<?php

namespace App\Http\Requests\Charge;

use App\Concerns\ChargeValidationRules;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChargeCreateRequest extends FormRequest
{
    use ChargeValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN->value;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->chargeRules();
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('last_payment_date') === '') {
            $this->merge(['last_payment_date' => null]);
        }
    }
}
