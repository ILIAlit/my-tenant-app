<?php

namespace App\Http\Requests\UtilityReadings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UtilityReadingRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function all($keys = null)
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
            'id' => 'required|integer|exists:utility_readings,id',
            'rejection_reason' => 'required|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'ID показаний обязателен',
            'id.exists' => 'Показания не найдены',
            'rejection_reason.required' => 'Укажите причину отклонения',
            'rejection_reason.max' => 'Причина не должна превышать 1000 символов',
        ];
    }
}
