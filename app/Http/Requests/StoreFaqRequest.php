<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:64'],
            'question' => ['required', 'array'],
            'question.*' => ['required', 'string'],
            'answer' => ['required', 'array'],
            'answer.*' => ['required', 'string'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
