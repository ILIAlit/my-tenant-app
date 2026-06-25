<?php

namespace App\Http\Requests\Charge;

use App\Concerns\ChargeValidationRules;
use App\Enums\UserRole;
use App\Models\Charge;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChargeUpdateRequest extends FormRequest
{
    use ChargeValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all($keys = null): array
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');

        return $data;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Charge::class, 'id')],
            ...$this->chargeRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('last_payment_date') === '') {
            $this->merge(['last_payment_date' => null]);
        }
    }
}
