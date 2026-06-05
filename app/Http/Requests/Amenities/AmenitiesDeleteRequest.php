<?php

namespace App\Http\Requests\Amenities;

use App\Concerns\NewsValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AmenitiesDeleteRequest extends FormRequest
{
    use NewsValidationRules;

    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        $data['rooms_id'] = $this->route('rooms_id');
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:amenities,id',
            'rooms_id' => 'required|exists:rooms,id',
        ];
    }
}
