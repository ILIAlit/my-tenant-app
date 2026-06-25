<?php

namespace App\Http\Requests\MeterReading;

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeterReadingStoreRequest extends FormRequest
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
        $data['user_id'] = $this->user()?->id;

        return $data;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::in(MeterType::meteredValues()),
                Rule::unique('meter_readings', 'type')
                    ->where(fn ($query) => $query
                        ->where('user_id', $this->user()?->id)
                        ->whereDate('reading_date', $this->input('reading_date'))
                        ->where('is_initial', false)
                        ->whereIn('status', [
                            MeterReadingStatus::Pending->value,
                            MeterReadingStatus::Approved->value,
                        ])),
            ],
            'reading_date' => ['required', 'date'],
            'value' => ['required', 'numeric', 'min:0', 'max:999999999.999'],
        ];
    }
}
