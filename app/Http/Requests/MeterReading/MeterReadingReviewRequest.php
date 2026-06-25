<?php

namespace App\Http\Requests\MeterReading;

use App\Enums\MeterReadingStatus;
use App\Enums\UserRole;
use App\Models\MeterReading;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MeterReadingReviewRequest extends FormRequest
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
            'id' => ['required', 'integer', Rule::exists(MeterReading::class, 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $reading = MeterReading::query()->find($this->route('id'));

            if ($reading === null) {
                return;
            }

            if ($reading->is_initial) {
                $validator->errors()->add('id', __('Начальные показания не требуют подтверждения.'));

                return;
            }

            if ($reading->status !== MeterReadingStatus::Pending) {
                $validator->errors()->add('id', __('Показание уже обработано.'));
            }
        });
    }
}
