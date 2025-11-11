<?php

declare(strict_types = 1);

namespace App\Enums;

enum AlertChannels: string
{
    case EMAIL = 'email';
    //case SMS      = 'sms';
    case WHATSAPP = 'whatsapp';
    // case TELEGRAM = 'telegram';
    // case DISCORD  = 'discord';

    public static function labels(): array
    {
        return [
            self::EMAIL->value => 'Email',
            //self::SMS->value      => 'SMS',
            self::WHATSAPP->value => 'WhatsApp',
            //self::TELEGRAM->value => 'Telegram',
            //self::DISCORD->value  => 'Discord',
        ];
    }
}
