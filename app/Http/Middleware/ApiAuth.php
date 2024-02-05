<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return new JsonResponse(
                [
                    'message' => 'Login failed',
                ],
                403
            );
        }

        $user = User::where('token', $token)->first();

        if (!$user) {
            return new JsonResponse(
                [
                    'message' => 'Login failed',
                ],
                403
            );
        }
        return $next($request);
    }
}
