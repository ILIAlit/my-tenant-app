<?php

namespace App\Http\Requests\Renter;

use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Room;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RenterAssignRoomRequest extends FormRequest
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
            'id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::RENTER->value),
            ],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('room_id') === '') {
            $this->merge(['room_id' => null]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $roomId = $this->input('room_id');

            if ($roomId === null || $roomId === '') {
                return;
            }

            $room = Room::query()->find($roomId);

            if ($room === null) {
                return;
            }

            if ($room->status === RoomStatus::Repair) {
                $validator->errors()->add('room_id', __('Нельзя назначить арендатора в комнату на ремонте.'));
            }
        });
    }
}
