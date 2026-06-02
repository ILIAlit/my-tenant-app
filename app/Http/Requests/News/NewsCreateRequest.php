<?php

namespace App\Http\Requests\News;

use App\Concerns\NewsValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NewsCreateRequest extends FormRequest
{
    use NewsValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->newsCreateRules();
    }
}
