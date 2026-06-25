<?php

namespace App\Http\Requests\Payment;

use App\Enums\ChargeStatus;
use App\Enums\UserRole;
use App\Models\Charge;
use App\Services\ChargePaymentService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::RENTER->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all($keys = null): array
    {
        $data = parent::all($keys);
        $data['charge_id'] = $this->route('chargeId');

        return $data;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'charge_id' => [
                'required',
                'integer',
                Rule::exists('charges', 'id')->where('user_id', $this->user()?->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $charge = Charge::query()->find($this->route('chargeId'));

            if ($charge === null) {
                return;
            }

            if ($charge->status === ChargeStatus::Paid) {
                $validator->errors()->add(
                    'charge_id',
                    __('Это начисление уже оплачено.'),
                );

                return;
            }

            $remaining = app(ChargePaymentService::class)->remainingAmount($charge);

            if ($remaining <= 0) {
                $validator->errors()->add(
                    'charge_id',
                    __('По этому начислению нет задолженности.'),
                );

                return;
            }

            if ((float) $this->input('amount') > $remaining) {
                $validator->errors()->add(
                    'amount',
                    __('Сумма превышает остаток к оплате (:amount BYN).', [
                        'amount' => number_format($remaining, 2, '.', ''),
                    ])
                );
            }
        });
    }
}
