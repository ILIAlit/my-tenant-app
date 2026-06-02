<?php

namespace App\Http\Requests\News;

use App\Concerns\NewsValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class NewsUpdateRequest extends FormRequest
{
    use NewsValidationRules;

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:news,id',
            ...$this->newsCreateRules(),
        ];
    }
}
