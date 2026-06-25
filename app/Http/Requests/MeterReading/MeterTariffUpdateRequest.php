<?php

namespace App\Http\Requests\MeterReading;

use App\Enums\MeterType;
use App\Enums\RoomType;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MeterTariffUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN->value;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (RoomType::cases() as $roomType) {
            foreach (MeterType::cases() as $type) {
                $rules["tariffs.{$roomType->value}.{$type->value}"] = [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999.9999',
                ];
            }
        }

        return $rules;
    }
}
