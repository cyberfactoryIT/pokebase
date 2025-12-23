<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardSearchRequest extends FormRequest
{
    /**
     * No authentication required for public card search
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for card search
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:80'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'q.required' => 'Search query is required',
            'q.min' => 'Search query must be at least 2 characters',
            'q.max' => 'Search query must not exceed 80 characters',
            'limit.integer' => 'Limit must be a valid integer',
            'limit.min' => 'Limit must be at least 1',
            'limit.max' => 'Limit must not exceed 50',
        ];
    }

    /**
     * Get validated query string (trimmed)
     */
    public function getQuery(): string
    {
        return trim($this->input('q'));
    }

    /**
     * Get validated limit with default
     */
    public function getLimit(): int
    {
        return (int) ($this->input('limit', 12));
    }
}
