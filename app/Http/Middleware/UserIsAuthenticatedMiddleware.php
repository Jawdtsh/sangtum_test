<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserIsAuthenticatedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'يبدو لانك لم تقم بتسجيل الدخول الى الموقع حتى الان, ارجو ان تقوم بالتسجيل للمتابعة ',
                'code' => 401
            ], 401);
        }

        return $next($request);
    }
}
