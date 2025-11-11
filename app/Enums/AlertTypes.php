<?php

declare(strict_types = 1);

namespace App\Enums;

enum AlertTypes: string
{
    case ERROR     = 'error';
    case WARNING   = 'warning';
    case INFO      = 'info';
    case DEBUG     = 'debug';
    case CRITICAL  = 'critical';
    case SUCCESS   = 'success';
    case UNKNOWN   = 'unknown';
    case FATAL     = 'fatal';
    case NOTICE    = 'notice';
    case ALERT     = 'alert';
    case EMERGENCY = 'emergency';
}
