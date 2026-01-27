<?php

namespace App\Services;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\TransientToken;

class AuthServices extends Services
{
    public function __construct(
        protected UserServices $userServices
    ) {}

    public function checkUser(LoginRequest $request)
    {
        try {
            $data = $request->validated();

            if (! Auth::attempt([
                'email' => $data['email'],
                'password' => $data['password'],
            ])) {
                return $this->errorResponse(
                    message: 'Thông tin đăng nhập không chính xác',
                    code: 401
                );
            }

            $user = Auth::user();

            $deviceName = $data['device_name'] ?? 'api';
            $tokenData = $this->userServices->createToken($user, $deviceName);

            return $this->successResponse(
                data: $tokenData,
                message: 'Đăng nhập thành công'
            );
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function allUserLoginDevices(Request $request)
    {
        try {
            $user = $request->user('sanctum');

            if (! $user) {
                return $this->errorResponse('Chưa đăng nhập', 401);
            }

            $currentTokenId = $user->currentAccessToken()?->id;

            $tokens = $user->tokens()
                ->select(['id', 'name', 'last_used_at', 'expires_at', 'created_at'])
                ->orderByDesc('last_used_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($t) use ($currentTokenId) {
                    return [
                        'id' => (string) $t->id,
                        'device_name' => $t->name,
                        'last_used_at' => $t->last_used_at?->toDateTimeString(),
                        'expires_at' => $t->expires_at?->toDateTimeString(),
                        'created_at' => $t->created_at?->toDateTimeString(),
                        'is_current' => (string) $t->id === (string) $currentTokenId,
                    ];
                });

            return $this->successResponse($tokens, 'Danh sách thiết bị đăng nhập');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user('sanctum');

            if (! $user) {
                return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
            }

            $token = $user->currentAccessToken();

            if (! $token || $token instanceof TransientToken) {
                return $this->errorResponse(
                    message: 'Bạn phải gửi Authorization: Bearer <token>',
                    code: 401
                );
            }

            PersonalAccessToken::query()
                ->where('id', $token->id)
                ->delete();

            return $this->successResponse(data: null, message: 'Đăng xuất thành công');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function logoutAll(Request $request)
    {
        try {
            $user = $request->user('sanctum');
            if (! $user) {
                return $this->errorResponse('Chưa đăng nhập', 401);
            }

            $user->tokens()->delete();

            return $this->successResponse(null, 'Đăng xuất tất cả thiết bị thành công');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function logoutDevice(Request $request, string $tokenId)
    {
        try {
            $user = $request->user('sanctum');

            if (! $user) {
                return $this->errorResponse('Chưa đăng nhập', 401);
            }

            $token = $user->tokens()->where('id', $tokenId)->first();

            if (! $token) {
                return $this->errorResponse('Thiết bị không tồn tại', 404);
            }

            $deviceName = $token->name;

            $token->delete();

            return $this->successResponse(
                null,
                "Đăng xuất thiết bị {$deviceName} thành công"
            );
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function sendEmail(
        ForgotPasswordRequest $request
    ) {
        try {
            $email = $request->validated()['email'];

            $status = Password::sendResetLink(['email' => $email]);

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(
                    data: null,
                    message: 'Đã gửi mã đặt lại mật khẩu đến email của bạn'
                );
            }

            if ($status === Password::RESET_THROTTLED) {
                return $this->errorResponse(
                    message: 'Vui lòng đợi ít phút trước khi gửi lại mã',
                    code: 429
                );
            }

            return $this->errorResponse(
                message: 'Không thể gửi mã đặt lại mật khẩu',
                code: 400
            );
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function reset(
        ResetPasswordRequest $request
    ) {
        try {
            $data = $request->validated();

            $status = Password::reset(
                [
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'token' => $data['token'],
                ],
                function ($user, $password) {
                    $user->forceFill([
                        'password' => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    $user->tokens()->delete();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(
                    data: null,
                    message: 'Đặt lại mật khẩu thành công'
                );
            }

            if ($status === Password::INVALID_TOKEN) {
                return $this->errorResponse(
                    message: 'Mã đặt lại mật khẩu không hợp lệ hoặc đã hết hạn',
                    code: 400
                );
            }

            if ($status === Password::INVALID_USER) {
                return $this->errorResponse(
                    message: 'Email không tồn tại trong hệ thống',
                    code: 404
                );
            }

            return $this->errorResponse(
                message: 'Không thể đặt lại mật khẩu',
                code: 400
            );

        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function changePassword(
        ChangePasswordRequest $request
    ) {
        try {
            $user = $request->user('sanctum');

            if (! $user) {
                return $this->errorResponse('Chưa đăng nhập', 401);
            }

            $data = $request->validated();

            if (! Hash::check($data['current_password'], $user->password)) {
                return $this->errorResponse('Mật khẩu hiện tại không đúng', 400);
            }

            $user->forceFill([
                'password' => $data['password'],
                'remember_token' => Str::random(60),
            ])->save();

            $user->tokens()->delete();

            return $this->successResponse(
                data: null,
                message: 'Đổi mật khẩu thành công'
            );
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }
}
