<?php

declare(strict_types = 1);

namespace App\Http\Requests\Seller\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category'); // pega a categoria da rota
        $user     = Auth::user();

        return [

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->ignore($category->id) // ignora a própria categoria
                    ->where(fn ($query) => $query->where('user_id', $user->id)),

            ],
            'description' => 'nullable|string|max:1000',
            'status'      => 'boolean',
        ];
    }
}
