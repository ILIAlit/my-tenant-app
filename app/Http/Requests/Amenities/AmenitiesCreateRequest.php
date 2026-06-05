<?php

namespace App\Http\Requests\Amenities;

use App\Concerns\NewsValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AmenitiesCreateRequest extends FormRequest
{
    use NewsValidationRules;

    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'price' => 'required|integer|min:0',
            'rooms_id' => 'required|exists:rooms,id',
        ];
    }
}
