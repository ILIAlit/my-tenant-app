<?php

namespace App\Http\Requests\Room;

use App\Concerns\RoomValidationRules;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RoomCreateRequest extends FormRequest
{
    use RoomValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN->value;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->roomRules();
    }

    protected function prepareForValidation(): void
    {
        $this->prepareRoomFloorInput();
    }
}
