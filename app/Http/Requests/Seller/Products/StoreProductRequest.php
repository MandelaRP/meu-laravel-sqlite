<?php

declare(strict_types = 1);

namespace App\Http\Requests\Seller\Products;

use App\Enums\Seller\ProductTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type'        => 'required|in:' . implode(',', ProductTypeEnum::values()),
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Converter vírgula para ponto no preço
        if ($this->has('price')) {
            $price = str_replace(',', '.', (string) $this->input('price'));
            $this->merge([
                'price' => $price,
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'name.max'      => 'O nome do produto não pode ter mais de 255 caracteres.',
            'type.required' => 'O tipo do produto é obrigatório.',
            'type.in'       => 'O tipo do produto deve ser Digital ou Físico.',
            'price.required' => 'O preço do produto é obrigatório.',
            'price.numeric' => 'O preço deve ser um número válido.',
            'price.min'     => 'O preço deve ser maior ou igual a zero.',
            'image.image'   => 'O arquivo deve ser uma imagem.',
            'image.mimes'   => 'A imagem deve ser do tipo: jpeg, png, jpg ou webp.',
            'image.max'     => 'A imagem não pode ter mais de 5MB.',
        ];
    }
}

