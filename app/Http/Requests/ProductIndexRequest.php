<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:100',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => 'The per_page parameter must be a number.',
            'per_page.min' => 'You must request at least 1 item per page.',
            'per_page.max' => 'You cannot request more than 100 items per page.',
        ];
    }
}
