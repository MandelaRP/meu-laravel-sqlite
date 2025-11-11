<?php

declare(strict_types = 1);

namespace App\Http\Requests\Seller\Checkout;

use App\Enums\Seller\CheckoutLayoutEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UpdateCheckoutRequest extends FormRequest
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
        return [
            'product_id'          => 'sometimes|required|exists:products,id',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'order_bump_ids'      => 'nullable|array',
            'order_bump_ids.*'    => 'exists:products,id',
            'layout'              => 'sometimes|required|in:' . implode(',', CheckoutLayoutEnum::values()),
            'banner'              => [
                'nullable',
                function ($attribute, $value, $fail): void {
                    if (request()->hasFile('banner')) {
                        $rules     = ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
                        $validator = Validator::make([$attribute => $value], [$attribute => $rules]);

                        if ($validator->fails()) {
                            foreach ($validator->errors()->get($attribute) as $message) {
                                $fail($message);
                            }
                        }
                    } elseif (! is_string($value)) {
                        $fail('O banner deve ser uma imagem válida ou uma URL de imagem.');
                    }
                },
            ],
            'countdown_enabled'            => 'boolean',
            'countdown_icon'               => 'nullable|string|max:50',
            'countdown_duration'           => 'nullable|integer|min:900|max:86400',
            'countdown_bg_color'           => 'nullable|string|size:7',
            'countdown_text_color'         => 'nullable|string|size:7',
            'countdown_message'            => 'nullable|string|max:255',
            'button_primary_color'         => 'nullable|string|size:7',
            'button_secondary_color'       => 'nullable|string|size:7',
            'button_hover_primary_color'   => 'nullable|string|size:7',
            'button_hover_secondary_color' => 'nullable|string|size:7',
            'form_fields_config'           => 'nullable|array',
            'form_requirements'            => 'nullable|array',
            'background_color'             => 'nullable|string|size:7',
            'text_color'                   => 'nullable|string|size:7',
            'stepped_form_enabled'         => 'boolean',
            'steps'                        => 'nullable|array',
            'payment_methods'              => 'nullable|array',
            // Order Bump Customization
            'order_bump_enabled'           => 'boolean',
            'order_bump_bg_color'          => 'nullable|string|size:7',
            'order_bump_text_color'        => 'nullable|string|size:7',
            'order_bump_border_color'      => 'nullable|string|size:7',
            'order_bump_description'       => 'nullable|string',
            'order_bump_cta_text'          => 'nullable|string|max:255',
            'order_bump_cta_bg_color'      => 'nullable|string|size:7',
            'order_bump_cta_text_color'    => 'nullable|string|size:7',
            'order_bump_recommended_text'  => 'nullable|string|max:255',
            'order_bump_recommended_color' => 'nullable|string|size:7',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required'               => 'O produto é obrigatório.',
            'product_id.exists'                 => 'O produto selecionado não existe.',
            'discount_percentage.min'           => 'O desconto deve ser no mínimo 0%.',
            'discount_percentage.max'           => 'O desconto deve ser no máximo 100%.',
            'order_bump_ids.*.exists'           => 'Um dos produtos adicionais selecionados não existe.',
            'layout.required'                   => 'O layout é obrigatório.',
            'layout.in'                         => 'O layout selecionado é inválido.',
            'banner.image'                      => 'O banner deve ser uma imagem.',
            'banner.mimes'                      => 'O banner deve ser do tipo: jpeg, png, jpg, gif ou svg.',
            'banner.max'                        => 'O banner não pode ter mais que 2MB.',
            'countdown_duration.min'            => 'A duração mínima da contagem regressiva é de 15 minutos.',
            'countdown_duration.max'            => 'A duração máxima da contagem regressiva é de 24 horas.',
            'countdown_icon.max'                => 'O emoji da contagem regressiva não pode ter mais que 50 caracteres.',
            'countdown_message.max'             => 'A mensagem da contagem regressiva não pode ter mais que 255 caracteres.',
            'countdown_bg_color.size'           => 'A cor de fundo da contagem regressiva deve ter 7 caracteres (ex: #ff0000).',
            'countdown_text_color.size'         => 'A cor do texto da contagem regressiva deve ter 7 caracteres (ex: #ffffff).',
            'button_primary_color.size'         => 'A cor do botão primário deve ter 7 caracteres (ex: #2563eb).',
            'button_secondary_color.size'       => 'A cor do botão secundário deve ter 7 caracteres (ex: #6b7280).',
            'button_hover_primary_color.size'   => 'A cor do hover do botão primário deve ter 7 caracteres (ex: #1d4ed8).',
            'button_hover_secondary_color.size' => 'A cor do hover do botão secundário deve ter 7 caracteres (ex: #4b5563).',
            'background_color.size'             => 'A cor de fundo deve ter 7 caracteres (ex: #ffffff).',
            'text_color.size'                   => 'A cor do texto deve ter 7 caracteres (ex: #000000).',
            // Order Bump Customization
            'order_bump_bg_color.size'          => 'A cor de fundo do order bump deve ter 7 caracteres (ex: #ffffff).',
            'order_bump_text_color.size'        => 'A cor do texto do order bump deve ter 7 caracteres (ex: #0f172a).',
            'order_bump_border_color.size'      => 'A cor da borda do order bump deve ter 7 caracteres (ex: #fbbf24).',
            'order_bump_description.max'        => 'A descrição do order bump não pode ter mais que 255 caracteres.',
            'order_bump_cta_text.max'           => 'O texto do CTA do order bump não pode ter mais que 255 caracteres.',
            'order_bump_cta_bg_color.size'      => 'A cor de fundo do CTA do order bump deve ter 7 caracteres (ex: #10b981).',
            'order_bump_cta_text_color.size'    => 'A cor do texto do CTA do order bump deve ter 7 caracteres (ex: #ffffff).',
            'order_bump_recommended_text.max'   => 'O texto de recomendação do order bump não pode ter mais que 255 caracteres.',
            'order_bump_recommended_color.size' => 'A cor do texto de recomendação do order bump deve ter 7 caracteres (ex: #fbbf24).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id'                   => 'produto',
            'discount_percentage'          => 'desconto',
            'order_bump_ids'               => 'produtos adicionais',
            'layout'                       => 'layout',
            'banner'                       => 'banner',
            'countdown_enabled'            => 'contagem regressiva',
            'countdown_icon'               => 'emoji da contagem regressiva',
            'countdown_duration'           => 'duração da contagem regressiva',
            'countdown_bg_color'           => 'cor de fundo da contagem regressiva',
            'countdown_text_color'         => 'cor do texto da contagem regressiva',
            'countdown_message'            => 'mensagem da contagem regressiva',
            'button_primary_color'         => 'cor do botão primário',
            'button_secondary_color'       => 'cor do botão secundário',
            'button_hover_primary_color'   => 'cor do hover do botão primário',
            'button_hover_secondary_color' => 'cor do hover do botão secundário',
            'form_fields_config'           => 'configuração dos campos do formulário',
            'form_requirements'            => 'requisitos do formulário',
            'background_color'             => 'cor de fundo',
            'text_color'                   => 'cor do texto',
            'stepped_form_enabled'         => 'formulário em etapas',
            'steps'                        => 'passos',

            'payment_icon_colors' => 'cores dos ícones de pagamento',
            // Order Bump Customization
            'order_bump_enabled'           => 'order bump',
            'order_bump_bg_color'          => 'cor de fundo do order bump',
            'order_bump_text_color'        => 'cor do texto do order bump',
            'order_bump_border_color'      => 'cor da borda do order bump',
            'order_bump_description'       => 'descrição do order bump',
            'order_bump_cta_text'          => 'texto do CTA do order bump',
            'order_bump_cta_bg_color'      => 'cor de fundo do CTA do order bump',
            'order_bump_cta_text_color'    => 'cor do texto do CTA do order bump',
            'order_bump_recommended_text'  => 'texto de recomendação do order bump',
            'order_bump_recommended_color' => 'cor do texto de recomendação do order bump',
        ];
    }
}
