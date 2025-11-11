<?php

declare(strict_types = 1);

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    /*
    * Scope para o usuário
    */
    protected static function booted()
    {
        static::addGlobalScope(new UserScope());
    }

    /*
    * Relação com o usuário
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
