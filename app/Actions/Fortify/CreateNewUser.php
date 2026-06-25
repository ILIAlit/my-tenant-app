<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
            'name' => ['required', 'string', 'max:255'],
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $loginBase = Str::slug(Str::before($input['email'], '@'), '_') ?: 'user';
        $login = $loginBase;
        $suffix = 1;

        while (User::query()->where('login', $login)->exists()) {
            $login = $loginBase.'_'.$suffix;
            $suffix++;
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'login' => $login,
            'password' => $input['password'],
            'role' => UserRole::RENTER,
        ]);
    }
}
