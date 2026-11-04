<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $allowedRoutes = [
                'api/v1/auth/me',
                'api/v1/auth/change-password',
                'api/v1/auth/logout',
            ];

            if (! in_array($request->path(), $allowedRoutes)) {
                return response()->json([
                    'message' => 'Silakan ganti password terlebih dahulu.',
                    'must_change_password' => true,
                ], 403);
            }
        }

        return $next($request);
    }
}