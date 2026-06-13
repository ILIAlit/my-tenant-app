<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContractIdRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:contracts,id',
            'rooms_id' => 'required|integer|exists:rooms,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'ID договора обязателен',
            'id.exists' => 'Договор не найден',
            'rooms_id.required' => 'ID комнаты обязателен',
            'rooms_id.exists' => 'Комната не найдена',
        ];
    }
}
