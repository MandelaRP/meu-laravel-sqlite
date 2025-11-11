<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\AlertTypes;
use App\Enums\Roles;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'alert_type',
        'is_active',
        'notifications',
        'alert_channels',
    ];

    protected $casts = [
        'alert_type'    => AlertTypes::class,
        'is_active'     => 'boolean',
        'notifications' => 'boolean',
        'role'          => Roles::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope(new UserScope());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
