<?php

declare(strict_types = 1);

namespace App\Enums\Seller;

enum ProductTypeEnum
{
    case FISICAL;
    case DIGITAL;

    public function label(): string
    {
        return match ($this) {
            self::FISICAL => 'Produto Físico',
            self::DIGITAL => 'Produto Digital',
        };
    }

    public static function values(): array
    {
        return [
            self::FISICAL->name,
            self::DIGITAL->name,
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::FISICAL => 'bg-yellow-500',
            self::DIGITAL => 'bg-green-500',
        };
    }
}
