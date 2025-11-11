<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acquirer extends Model
{
    /** @use HasFactory<\Database\Factories\AcquirerFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'api_status',
        'gateway_fee_percentage',
        'fixed_fee',
        'percentage_fee',
        'withdrawal_fee',
        'credentials',
        'settings',
        'logo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gateway_fee_percentage' => 'decimal:2',
            'fixed_fee' => 'decimal:2',
            'percentage_fee' => 'decimal:2',
            'withdrawal_fee' => 'decimal:2',
            'credentials' => 'array',
            'settings' => 'array',
        ];
    }
}
