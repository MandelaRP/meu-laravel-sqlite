<?php

declare(strict_types = 1);

namespace App\Enums\Seller;

enum CheckoutLayoutEnum: string
{
    case SINGLE     = 'single';
    case TWO_COLUMN = 'two-column';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE     => 'Uma coluna',
            self::TWO_COLUMN => 'Duas colunas',
        };
    }

    public static function values(): array
    {
        return [
            self::SINGLE->value,
            self::TWO_COLUMN->value,
        ];
    }
}
