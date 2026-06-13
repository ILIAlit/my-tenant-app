<?php

namespace App\Http\Requests\UtilityReadings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UtilityReadingUpdateRequest extends FormRequest
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
            'id' => 'required|integer|exists:utility_readings,id',
            'cold_water' => 'nullable|numeric|min:0',
            'hot_water' => 'nullable|numeric|min:0',
            'electricity' => 'nullable|numeric|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $validated = $validator->validated();

                $coldWater = $this->input('cold_water');
                $hotWater = $this->input('hot_water');
                $electricity = $this->input('electricity');

                if ($coldWater === null && $hotWater === null && $electricity === null) {
                    $validator->errors()->add('cold_water', 'Укажите хотя бы одно показание');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'Показание не найдено',
            'id.exists' => 'Показание не найдено',
            'cold_water.numeric' => 'Показание холодной воды должно быть числом',
            'hot_water.numeric' => 'Показание горячей воды должно быть числом',
            'electricity.numeric' => 'Показание электроэнергии должно быть числом',
        ];
    }
}
