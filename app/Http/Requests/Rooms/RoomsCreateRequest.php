<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;

class RoomsCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'number' => 'required|integer|min:1|unique:rooms,number',
            'floor' => 'required|integer|min:0|max:100',
            'square' => 'required|numeric|min:0.1',
            'status' => 'required|in:free,used,repair',
            'date_of_last_repair' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'number.required' => 'Номер комнаты обязателен',
            'number.integer' => 'Номер комнаты должен быть числом',
            'number.unique' => 'Комната с таким номером уже существует',
            'floor.required' => 'Этаж обязателен',
            'floor.integer' => 'Этаж должен быть числом',
            'square.required' => 'Площадь обязательна',
            'square.numeric' => 'Площадь должна быть числом',
            'status.required' => 'Статус обязателен',
            'status.in' => 'Некорректный статус',
            'date_of_last_repair.date' => 'Неверный формат даты',
            'notes.max' => 'Примечания не должны превышать 1000 символов',
        ];
    }
}
