<?php

namespace App\Concerns;

use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Room;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

trait RoomValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function roomRules(?int $roomId = null): array
    {
        return [
            'type' => ['required', new Enum(RoomType::class)],
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique(Room::class, 'number')
                    ->where('type', $this->input('type'))
                    ->ignore($roomId),
            ],
            'floor' => [
                Rule::requiredIf(fn (): bool => $this->input('type') === RoomType::Room->value),
                'nullable',
                'integer',
                'min:0',
                'max:255',
            ],
            'area' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'status' => ['required', new Enum(RoomStatus::class)],
            'last_repair_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareRoomFloorInput(): void
    {
        if ($this->input('floor') === '' || $this->input('floor') === null) {
            $this->merge(['floor' => null]);
        }
    }
}
