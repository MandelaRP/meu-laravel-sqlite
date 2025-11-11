<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            // Informações Básicas
            'person_type' => 'required|string|max:255',
            'full_name'   => 'required|string|max:255',
            'phone'       => 'required|string|max:255',
            'document'    => 'required|string|max:255',

            // Informações Comerciais

            'average_revenue' => 'required|string|max:255',
            'average_ticket'  => 'required|string|max:255',
            'products'        => 'required|string|max:255',

            // Endereço
            'address'  => 'required|string|max:255',
            'number'   => 'required|string|max:255',
            'city'     => 'required|string|max:255',
            'state'    => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',

            // Documentos de Identificação
            'rg_cnh_frente' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'rg_cnh_verso'  => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'selfie'        => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ];

        // Regras específicas para Pessoa Jurídica
        if ($this->input('person_type') === 'pj') {
            $rules['social_contract'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['social_reason']   = 'required_if:person_type,pj|string|max:255';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $messages = [
            // Mensagens para Informações Básicas
            'person_type.required' => 'O tipo de pessoa é obrigatório',
            'full_name.required'   => 'O nome completo é obrigatório',
            'phone.required'       => 'O telefone é obrigatório',
            'document.required'    => 'O documento é obrigatório',

            // Mensagens para Informações Comerciais
            'average_revenue.required' => 'O faturamento médio mensal é obrigatório',
            'average_ticket.required'  => 'O ticket médio é obrigatório',
            'products.required'        => 'Os produtos comercializados são obrigatórios',
            'products.max'             => 'Os produtos comercializados devem ter no máximo 255 caracteres',

            // Mensagens para Endereço
            'address.required'  => 'O endereço é obrigatório',
            'address.max'       => 'O endereço deve ter no máximo 255 caracteres',
            'number.required'   => 'O número do endereço é obrigatório',
            'number.max'        => 'O número deve ter no máximo 255 caracteres',
            'city.required'     => 'A cidade é obrigatória',
            'city.max'          => 'A cidade deve ter no máximo 255 caracteres',
            'state.required'    => 'O estado é obrigatório',
            'state.max'         => 'O estado deve ter no máximo 255 caracteres',
            'zip_code.required' => 'O CEP é obrigatório',
            'zip_code.max'      => 'O CEP deve ter no máximo 255 caracteres',

            // Mensagens para Documentos de Identificação
            'rg_cnh_frente.required' => 'O RG/CNH frente é obrigatório',
            'rg_cnh_frente.file'     => 'O RG/CNH frente deve ser um arquivo',
            'rg_cnh_frente.mimes'    => 'O RG/CNH frente deve ser uma imagem do tipo: jpg, jpeg ou png',
            'rg_cnh_frente.max'      => 'O RG/CNH frente não pode ser maior que 5MB',
            'rg_cnh_verso.required'  => 'O RG/CNH verso é obrigatório',
            'rg_cnh_verso.file'      => 'O RG/CNH verso deve ser um arquivo',
            'rg_cnh_verso.mimes'     => 'O RG/CNH verso deve ser uma imagem do tipo: jpg, jpeg ou png',
            'rg_cnh_verso.max'       => 'O RG/CNH verso não pode ser maior que 5MB',
            'selfie.required'        => 'A selfie é obrigatória',
            'selfie.file'            => 'A selfie deve ser um arquivo',
            'selfie.mimes'           => 'A selfie deve ser uma imagem do tipo: jpg, jpeg ou png',
            'selfie.max'             => 'A selfie não pode ser maior que 5MB',

            // Mensagens para Documentos de Pessoa Jurídica
            'social_contract.mimes' => 'O contrato social deve ser um arquivo do tipo: pdf, jpg, jpeg ou png',
            'social_contract.max'   => 'O contrato social não pode ser maior que 5MB',
        ];

        if ($this->input('person_type') === 'pj') {
            $messages['social_reason.required_if']   = 'A razão social é obrigatória para pessoa jurídica';
            $messages['social_contract.required_if'] = 'O contrato social é obrigatório para pessoa jurídica';
            $messages['social_contract.required']    = 'O contrato social é obrigatório';
            $messages['social_reason.required']      = 'A razão social é obrigatória';
            $messages['social_contract.file']        = 'O contrato social deve ser um arquivo';
        }

        return $messages;
    }
}
