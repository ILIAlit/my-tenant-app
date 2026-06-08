<?php

namespace App\Http\Requests\Payments;

use App\Concerns\NewsValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentsProcessRequest extends FormRequest
{
    use NewsValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoices_id' => 'required|exists:invoices,id',
            'amount' => 'required|min:0',
            'receipt' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Сумма не введена',
            'invoices_id.exists' => 'Комната не найдена',
            'amount.integer' => 'Сумма должна быть числом',
            'receipt.required' => 'Файл не прикреплён',
        ];
    }
}
