<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddQuantityProductsRequest extends FormRequest
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
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'products.required' => 'You must provide at least one product.',
            'products.array' => 'Products must be an array.',
            'products.min' => 'Order must contain at least one product.',

            'products.*.id.required' => 'Each product must have an ID.',
            'products.*.id.exists' => 'One or more selected products do not exist.',

            'products.*.quantity.required' => 'Please specify quantity for each product.',
            'products.*.quantity.integer' => 'Product quantity must be a whole number.',
            'products.*.quantity.min' => 'Product quantity must be at least 1.',
        ];
    }
}
