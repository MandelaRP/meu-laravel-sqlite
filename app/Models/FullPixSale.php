<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FullPixSale extends Model
{
    /** @use HasFactory<\Database\Factories\FullPixSaleFactory> */
    use HasFactory;

    protected $table = 'fullpix_sales';

    protected $fillable = [
        'user_id',
        'fullpix_transaction_id',
        'reference_code',
        'external_reference',
        'amount',
        'currency',
        'status',
        'pix_qrcode',
        'pix_qrcode_image',
        'expires_at',
        'paid_at',
        'metadata',
        'fullpix_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
        'fullpix_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Transaction::class, 'acquirer_ref', 'fullpix_transaction_id');
    }
}
