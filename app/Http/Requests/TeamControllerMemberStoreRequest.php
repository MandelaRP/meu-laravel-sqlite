<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamControllerMemberStoreRequest extends FormRequest
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
            'user_id'        => 'required|exists:users,id',
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'required|string|max:255',
            'role'           => 'required|string|max:255',
            'is_active'      => 'required|boolean|nullable',
            'notifications'  => 'required|boolean',
            'alert_channels' => 'required_if:notifications,true|array',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'               => 'O email já está em uso.',
            'phone.unique'               => 'O telefone já está em uso.',
            'role.required'              => 'A função é obrigatória.',
            'is_active.required'         => 'O status é obrigatório.',
            'notifications.required'     => 'As notificações são obrigatórias.',
            'alert_channels.required_if' => 'Selecione pelo menos um canal de notificação.',
        ];
    }
}
