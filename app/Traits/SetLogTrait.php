<?php

declare(strict_types = 1);

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait SetLogTrait
{
    /**
     * Define o log do sistema
     *
     * @param string $type (error, info, warning, debug, critical, alert, emergency)
     * @param array $data
     * @param \Throwable $error
     */
    private function setLog(string $channel, string $message, ?array $data = [], $error = null, string $type = 'error'): void
    {
        Log::channel($channel)->{$type}($message, [
            'error_message' => $error?->getMessage(),
            'line'          => $error?->getLine(),
            'user_id'       => Auth::id(),
            'user_name'     => Auth::user()->name,
            'user_phone'    => Auth::user()->phone,
            'data'          => $data,
            'file'          => $error?->getFile(),
            'trace'         => $error?->getTrace(),
        ]);
    }
}
