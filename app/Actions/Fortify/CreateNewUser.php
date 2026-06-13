<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'last_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
        ], [
            'last_name.required' => 'Фамилия обязательна',
            'name.required' => 'Имя обязательно',
            'email.required' => 'Email обязателен',
            'email.email' => 'Некорректный email',
            'email.unique' => 'Этот email уже зарегистрирован',
        ])->validate();

        return User::create([
            'last_name' => $input['last_name'],
            'name' => $input['name'],
            'middle_name' => $input['middle_name'] ?? null,
            'login' => $input['email'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => UserRole::RENTER,
        ]);
    }
}
