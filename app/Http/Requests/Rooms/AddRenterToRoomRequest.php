<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;

class AddRenterToRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['room_id'] = $this->route('room_id');
        $data['renter_id'] = $this->route('renter_id');
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'room_id' => 'required|integer|exists:rooms,id',
            'renter_id' => 'required|integer|exists:users,id',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'room_id.required' => 'ID комнаты обязателен',
            'room_id.exists' => 'Комната не найдена',
            'renter_id.required' => 'ID арендатора обязателен',
            'renter_id.exists' => 'Арендатор не найден',
        ];
    }
}
