<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContractCreateRequest extends FormRequest
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
            'rooms_id' => 'required|integer|exists:rooms,id',
            'number' => 'required|string|max:255',
            'conclusion_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:conclusion_date',
            'payment_terms' => 'required|string|max:5000',
            'termination_terms' => 'required|string|max:5000',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rooms_id.required' => 'Комната обязательна',
            'rooms_id.exists' => 'Комната не найдена',
            'number.required' => 'Номер договора обязателен',
            'conclusion_date.required' => 'Дата заключения обязательна',
            'expiration_date.required' => 'Срок действия обязателен',
            'expiration_date.after_or_equal' => 'Срок действия не может быть раньше даты заключения',
            'payment_terms.required' => 'Условия оплаты обязательны',
            'termination_terms.required' => 'Условия расторжения обязательны',
            'file.required' => 'Файл договора обязателен',
            'file.mimes' => 'Допустимы файлы: pdf, doc, docx, jpg, png',
            'file.max' => 'Файл не должен превышать 10 МБ',
        ];
    }
}
