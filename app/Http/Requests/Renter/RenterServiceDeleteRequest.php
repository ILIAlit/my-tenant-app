<?php

namespace App\Http\Requests\Renter;

use App\Enums\UserRole;
use App\Models\RenterService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenterServiceDeleteRequest extends FormRequest
{
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
        $data['service_id'] = $this->route('serviceId');

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
            'service_id' => [
                'required',
                'integer',
                Rule::exists(RenterService::class, 'id')->where('user_id', $renterId),
            ],
        ];
    }
}
