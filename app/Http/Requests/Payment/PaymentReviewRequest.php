<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PaymentReviewRequest extends FormRequest
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
        return [
            'id' => ['required', 'integer', Rule::exists(Payment::class, 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $paymentId = $this->route('id');
            $payment = Payment::query()->find($paymentId);

            if ($payment === null) {
                return;
            }

            if ($payment->status !== PaymentStatus::Pending) {
                $validator->errors()->add('id', __('Платёж уже обработан.'));
            }
        });
    }
}
