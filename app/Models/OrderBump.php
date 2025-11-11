<?php

declare(strict_types = 1);

namespace App\Models;

use App\Models\Seller\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderBump extends Model
{
    protected $fillable = [
        'checkout_id',
        'product_id',
    ];

    public function checkout(): BelongsTo
    {
        return $this->belongsTo(Checkout::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
