<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiberpaySale extends Model
{
    /** @use HasFactory<\Database\Factories\LiberpaySaleFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'liberpay_sale_id',
        'reference_code',
        'external_reference',
        'amount',
        'currency',
        'status',
        'pix_qr_code',
        'pix_qr_code_image',
        'expires_at',
        'paid_at',
        'metadata',
        'liberpay_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
        'liberpay_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Transaction::class, 'acquirer_ref', 'liberpay_sale_id');
    }
}
