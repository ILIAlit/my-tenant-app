<?php

namespace App\Http\Requests\UtilityReadings;

use App\Enums\UtilityReadingStatus;
use App\Models\Contracts;
use App\Models\Rooms;
use App\Models\UtilityReading;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UtilityReadingCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()->isRenter()) {
            return false;
        }

        $roomsId = (int) $this->input('rooms_id');

        return $this->user()->rooms()->where('id', $roomsId)->exists();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rooms_id' => 'required|integer|exists:rooms,id',
            'contracts_id' => 'required|integer|exists:contracts,id',
            'period_start' => 'required|date',
            'cold_water' => 'nullable|numeric|min:0',
            'hot_water' => 'nullable|numeric|min:0',
            'electricity' => 'nullable|numeric|min:0',
            'cold_water_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'hot_water_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'electricity_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $validated = $validator->validated();
                $room = Rooms::find($validated['rooms_id']);
                $contract = Contracts::find($validated['contracts_id']);

                if ($room === null || $contract === null) {
                    return;
                }

                if ($contract->rooms_id !== $room->id) {
                    $validator->errors()->add('contracts_id', 'Договор не относится к выбранной комнате');

                    return;
                }

                $period = $contract->findPeriodByStart($validated['period_start']);

                if ($period === null) {
                    $validator->errors()->add('period_start', 'Недопустимый расчётный период для этого договора');

                    return;
                }

                if ($period['start']->isFuture()) {
                    $validator->errors()->add('period_start', 'Показания можно вносить только после начала расчётного периода');

                    return;
                }

                $hasActiveReading = UtilityReading::query()
                    ->where('rooms_id', $room->id)
                    ->whereDate('period_start', $period['start'])
                    ->whereIn('status', [
                        UtilityReadingStatus::Review->value,
                        UtilityReadingStatus::Approved->value,
                    ])
                    ->exists();

                if ($hasActiveReading) {
                    $validator->errors()->add('period_start', 'Показания за этот период уже внесены');
                }

                $coldWater = $this->input('cold_water');
                $hotWater = $this->input('hot_water');
                $electricity = $this->input('electricity');

                if ($coldWater === null && $hotWater === null && $electricity === null) {
                    $validator->errors()->add('cold_water', 'Укажите хотя бы одно показание');
                }
            },
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
            'contracts_id.required' => 'Договор обязателен',
            'contracts_id.exists' => 'Договор не найден',
            'period_start.required' => 'Период обязателен',
            'cold_water.numeric' => 'Показание холодной воды должно быть числом',
            'hot_water.numeric' => 'Показание горячей воды должно быть числом',
            'electricity.numeric' => 'Показание электроэнергии должно быть числом',
            'cold_water_photo.mimes' => 'Фото холодной воды: допустимы jpg, png, webp',
            'hot_water_photo.mimes' => 'Фото горячей воды: допустимы jpg, png, webp',
            'electricity_photo.mimes' => 'Фото электросчётчика: допустимы jpg, png, webp',
            'cold_water_photo.max' => 'Фото не должно превышать 10 МБ',
            'hot_water_photo.max' => 'Фото не должно превышать 10 МБ',
            'electricity_photo.max' => 'Фото не должно превышать 10 МБ',
        ];
    }
}
