<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceUpdateRequest extends FormRequest
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
            'id' => 'required|integer|exists:invoices,id',
            'name' => 'required|string|max:255',
            'total_price' => 'required|integer|min:0',
            'create_date' => 'required|date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'ID начисления обязателен',
            'id.exists' => 'Начисление не найдено',
            'name.required' => 'Название обязательно',
            'total_price.required' => 'Сумма обязательна',
            'total_price.integer' => 'Сумма должна быть числом',
            'create_date.required' => 'Дата начисления обязательна',
        ];
    }
}
