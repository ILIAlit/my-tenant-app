<?php

namespace App\Http\Requests\MeterReading;

use App\Concerns\MeterReadingValidationRules;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MeterReadingCreateRequest extends FormRequest
{
    use MeterReadingValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN->value;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->meterReadingRules();
    }
}
