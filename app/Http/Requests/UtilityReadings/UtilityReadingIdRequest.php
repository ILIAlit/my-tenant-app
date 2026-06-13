<?php

namespace App\Http\Requests\UtilityReadings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UtilityReadingIdRequest extends FormRequest
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
            'rooms_id' => 'required|integer|exists:rooms,id',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
            'rooms_id' => $this->route('rooms_id'),
        ]);
    }
}
