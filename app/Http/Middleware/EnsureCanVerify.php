<?php

namespace App\Http\Middleware;

use App\Support\UtilityAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanVerify
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && UtilityAccess::canVerify($request->user())) {
            return $next($request);
        }

        abort(403, 'This feature is not available for your account type.');
    }
}
