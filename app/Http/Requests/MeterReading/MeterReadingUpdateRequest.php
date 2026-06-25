<?php

namespace App\Http\Requests\MeterReading;

use App\Concerns\MeterReadingValidationRules;
use App\Enums\UserRole;
use App\Models\MeterReading;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeterReadingUpdateRequest extends FormRequest
{
    use MeterReadingValidationRules;

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
            'user_id' => $this->meterReadingRules()['user_id'],
            'type' => $this->uniqueMeterReadingTypeRule((int) $this->route('id')),
            'reading_date' => $this->meterReadingRules()['reading_date'],
            'value' => $this->meterReadingRules()['value'],
        ];
    }
}
