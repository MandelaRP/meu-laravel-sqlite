<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutOrderBumpRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'order_bump_ids'   => ['nullable', 'array'],
            'order_bump_ids.*' => [
                'required',
                'string',
                'uuid',
                Rule::exists('products', 'id')->where(function ($query): void {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_bump_ids.*.exists' => 'O produto selecionado não pertence ao seu usuário.',
            'order_bump_ids.*.uuid'   => 'ID do produto deve ser um UUID válido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'order_bump_ids'   => 'order bumps',
            'order_bump_ids.*' => 'produto',
        ];
    }
}
