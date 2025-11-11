<?php

declare(strict_types = 1);

namespace App\Enums;

enum Roles: string
{
    case Admin          = 'admin';
    case Support        = 'support';
    case Developer      = 'developer';
    case Owner          = 'owner';
    case Infrastructure = 'infrastructure';

    public static function labels(): array
    {
        return [
            self::Admin->value          => 'Administrador',
            self::Support->value        => 'Suporte',
            self::Developer->value      => 'Desenvolvedor',
            self::Owner->value          => 'Proprietário',
            self::Infrastructure->value => 'Infraestrutura',
        ];
    }
}
