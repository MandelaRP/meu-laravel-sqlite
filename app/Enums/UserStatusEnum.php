<?php

declare(strict_types = 1);

namespace App\Enums;

enum UserStatusEnum: string
{
    case ACTIVE      = 'active';
    case INACTIVE    = 'inactive';
    case PENDING     = 'pending';
    case BLOCKED     = 'blocked';
    case RECENT_USER = 'recent_user';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE      => 'Active',
            self::INACTIVE    => 'Inactive',
            self::PENDING     => 'Pending',
            self::BLOCKED     => 'Blocked',
            self::RECENT_USER => 'Recent User',
        };
    }
}
