<?php

declare(strict_types = 1);

namespace App\Http\Requests\Seller\Checkout;

use App\Enums\Seller\CheckoutLayoutEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
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
        $user = $this->user();

        return [
            'product_id'                   => [
                'required',
                Rule::exists('products', 'id')->where('user_id', $user->id)->where('status', true),
            ],
            'discount_percentage'          => 'nullable|integer|min:0|max:100',
            'order_bump_ids'               => 'nullable|array',
            'layout'                       => 'required|in:' . implode(',', CheckoutLayoutEnum::values()),
            'banner'                       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
}
