<?php

namespace App\Http\Middleware;

use App\Traits\JsonResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use JsonResponse;

    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
        }

        $role = $user->role?->name;

        if (! $role || ! in_array($role, $roles, true)) {
            return $this->errorResponse(message: 'Không có quyền truy cập', code: 403);
        }

        return $next($request);
    }
}
