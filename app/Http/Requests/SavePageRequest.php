<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'content' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['title.required' => 'Une page doit avoir un titre.'];
    }
}
