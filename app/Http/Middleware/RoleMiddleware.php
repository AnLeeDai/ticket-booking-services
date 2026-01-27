<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập',
            ], 401);
        }

        $role = $user->role?->name;

        if (! $role || ! in_array($role, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền truy cập',
            ], 403);
        }

        return $next($request);
    }
}
