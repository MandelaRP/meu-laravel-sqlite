<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecentUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->status === UserStatusEnum::RECENT_USER->value) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
