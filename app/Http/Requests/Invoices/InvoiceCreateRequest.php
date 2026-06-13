<?php

namespace App\Http\Requests\Invoices;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceCreateRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
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
            'user_id.required' => 'Арендатор обязателен',
            'user_id.exists' => 'Арендатор не найден',
            'name.required' => 'Название обязательно',
            'total_price.required' => 'Сумма обязательна',
            'total_price.integer' => 'Сумма должна быть числом',
            'create_date.required' => 'Дата начисления обязательна',
        ];
    }
}
