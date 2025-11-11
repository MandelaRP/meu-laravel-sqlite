<?php

declare(strict_types = 1);

namespace App\Models;

use App\Models\Seller\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checkout extends Model
{
    use HasUuids;
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_bump_id',
        'checkout_template',
        'layout',
        'banner',
        'countdown_enabled',
        'countdown_icon',
        'countdown_duration',
        'countdown_bg_color',
        'countdown_text_color',
        'countdown_message',
        'countdown_expired',
        'button_primary_color',
        'button_secondary_color',
        'button_hover_primary_color',
        'button_hover_secondary_color',
        'background_color',
        'form_fields_config',
        'form_requirements',
        'stepped_form_enabled',
        'steps',
        'payment_methods',
        'payment_icon_colors',
        'discount_percentage',
        'order_bump_enabled',
        'order_bump_bg_color',
        'order_bump_text_color',
        'order_bump_border_color',
        'order_bump_description',
        'order_bump_cta_text',
        'order_bump_cta_bg_color',
        'order_bump_cta_text_color',
        'order_bump_recommended_text',
        'order_bump_recommended_color',
    ];

    protected $casts = [
        'form_fields_config'   => 'array',
        'form_requirements'    => 'array',
        'steps'                => 'array',
        'stepped_form_enabled' => 'boolean',
        'payment_methods'      => 'array',
        'payment_icon_colors'  => 'array',
        'countdown_enabled'    => 'boolean',
        'countdown_expired'    => 'boolean',
        'countdown_duration'   => 'integer',
        'discount_percentage'  => 'integer',
        'order_bump_enabled'   => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderBumps(): HasMany
    {
        return $this->hasMany(OrderBump::class);
    }
}
