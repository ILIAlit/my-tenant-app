<?php

namespace App\Http\Requests\Room;

use App\Concerns\RoomValidationRules;
use App\Enums\UserRole;
use App\Models\Room;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomUpdateRequest extends FormRequest
{
    use RoomValidationRules;

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
        $roomId = (int) $this->route('id');

        return [
            'id' => ['required', 'integer', Rule::exists('rooms', 'id')],
            ...$this->roomRules($roomId),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareRoomFloorInput();

        $room = Room::query()->find($this->route('id'));

        if ($room !== null && ! $this->has('type')) {
            $this->merge(['type' => $room->type->value]);
        }
    }
}
