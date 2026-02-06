<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $method = $request->method();
                $path = $request->path();

                if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
                    $segments = explode('/', trim($path, '/'));
                    $resource = end($segments);

                    if (! preg_match('/^[0-9a-f\-]{36}$|^\d+$/', $resource)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Thiếu ID trong URL. Sử dụng: {$method} /{$path}/{id}",
                        ], 400);
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => "Phương thức {$method} không được hỗ trợ cho route {$path}.",
                    'hint' => $e->getHeaders()['Allow'] ?? null,
                ], 405);
            }
        });

        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy route: '.$request->path(),
                ], 404);
            }
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập hoặc token đã hết hạn.',
                ], 401);
            }
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
    })->create();
