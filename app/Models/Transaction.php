<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'invoice',
        'payment_status',
        'total_amount',
        'payment_method',
        'net_deposit',
        'acquirer_ref',
        'date',
        'fee',
        'is_sample',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'net_deposit' => 'decimal:2',
        'fee' => 'decimal:2',
        'date' => 'date',
        'is_sample' => 'boolean',
    ];

    /**
     * Garantir que a data seja sempre a data atual quando criada
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Se a data não foi definida ou está vazia, usar a data atual
            if (empty($transaction->date)) {
                $transaction->date = today();
            } else {
                // Garantir que seja apenas a data, sem hora
                $transaction->date = \Carbon\Carbon::parse($transaction->date)->toDateString();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function liberpaySale(): BelongsTo
    {
        return $this->belongsTo(LiberpaySale::class, 'acquirer_ref', 'liberpay_sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Seller\Product::class);
    }
}
