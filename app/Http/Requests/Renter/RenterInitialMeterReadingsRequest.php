<?php

namespace App\Http\Requests\Renter;

use App\Enums\MeterType;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenterInitialMeterReadingsRequest extends FormRequest
{
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
        $rules = [
            'id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::RENTER->value),
            ],
        ];

        foreach (MeterType::metered() as $type) {
            $key = $type->value;

            $rules["readings.{$key}.value"] = ['nullable', 'numeric', 'min:0', 'max:999999999.999', "required_with:readings.{$key}.reading_date"];
            $rules["readings.{$key}.reading_date"] = ['nullable', 'date', "required_with:readings.{$key}.value"];
        }

        return $rules;
    }
}
