<?php

namespace App\Http\Requests\UtilityReadings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UtilityTariffUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cold_water_rate' => 'required|numeric|min:0',
            'hot_water_rate' => 'required|numeric|min:0',
            'electricity_rate' => 'required|numeric|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cold_water_rate.required' => 'Укажите тариф на холодную воду',
            'hot_water_rate.required' => 'Укажите тариф на горячую воду',
            'electricity_rate.required' => 'Укажите тариф на электроэнергию',
        ];
    }
}
