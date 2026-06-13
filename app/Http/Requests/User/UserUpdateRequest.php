<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function all($keys = null)
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
        $id = $this->route('id');

        return [
            'id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'login' => ['required', 'string', 'max:255', Rule::unique('users', 'login')->ignore($id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'ID обязателен',
            'id.exists' => 'Арендатор не найден',
            'name.required' => 'Имя обязательно',
            'email.required' => 'Email обязателен',
            'email.email' => 'Некорректный email',
            'email.unique' => 'Этот email уже используется',
            'login.required' => 'Логин обязателен',
            'login.unique' => 'Этот логин уже используется',
        ];
    }
}
