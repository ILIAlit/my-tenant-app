<?php

namespace App\Http\Requests\Renter;

use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RenterUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

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
        $renterId = (int) $this->route('id');

        return [
            'id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::RENTER->value),
            ],
            ...$this->profileRules($renterId),
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'login' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class)->ignore($renterId),
            ],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
        ];
    }
}
