<?php

namespace App\Http\Requests\Renter;

use App\Enums\UserRole;
use App\Models\Contract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenterContractRequest extends FormRequest
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
            'number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contracts', 'number')->ignore(
                    Contract::query()->where('user_id', $renterId)->value('id')
                ),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'remove_file' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('remove_file')) {
            $this->merge([
                'remove_file' => filter_var($this->input('remove_file'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
