<?php

namespace App\Concerns;

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait MeterReadingValidationRules
{
    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function uniqueMeterReadingTypeRule(?int $ignoreId = null): array
    {
        $rule = Rule::unique('meter_readings', 'type')
            ->where(fn ($query) => $query
                ->where('user_id', $this->integer('user_id'))
                ->whereDate('reading_date', $this->input('reading_date'))
                ->where('is_initial', false)
                ->whereIn('status', [
                    MeterReadingStatus::Pending->value,
                    MeterReadingStatus::Approved->value,
                ]));

        if ($ignoreId !== null) {
            $rule->ignore($ignoreId);
        }

        return [
            'required',
            Rule::in(MeterType::meteredValues()),
            $rule,
        ];
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function meterReadingRules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::RENTER->value),
            ],
            'type' => $this->uniqueMeterReadingTypeRule(),
            'reading_date' => ['required', 'date'],
            'value' => ['required', 'numeric', 'min:0', 'max:999999999.999'],
        ];
    }
}
