<?php

namespace App\Services;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Role;
use App\Models\User;
use App\Notifications\RegisterSuccessNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    /**
     * Đăng nhập.
     */
    public function login(LoginRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $data = $request->validated();

            if (! Auth::attempt([
                'email' => $data['email'],
                'password' => $data['password'],
            ])) {
                return $this->errorResponse(message: 'Thông tin đăng nhập không chính xác', code: 401);
            }

            $user = Auth::user();

            if ($user->status === 'UN_ACTIVE') {
                Auth::guard('web')->logout();

                return $this->errorResponse(message: 'Tài khoản đã bị khoá', code: 403);
            }

            $deviceName = $data['device_name'] ?? 'api';
            $tokenData = $this->userServices->createToken($user, $deviceName);

            return $this->successResponse(data: $tokenData, message: 'Đăng nhập thành công');
        });
    }

    /**
     * Đăng ký tài khoản.
     */
    public function register(RegisterRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $data = $request->validated();

            $username = $data['user_name'] ?? null;
            if (! $username) {
                $base = Str::lower(preg_replace('/[^a-z0-9]+/', '_', Str::before($data['email'], '@')) ?: 'user');
                $username = $this->buildUniqueUsername(trim($base, '_'));
            }

            $roleId = Role::query()->where('name', 'customer')->value('role_id');

            if (! $roleId) {
                return $this->errorResponse(message: 'Không tìm thấy role customer', code: 500);
            }

            $user = DB::transaction(fn () => User::query()->create([
                'role_id' => $roleId,
                'full_name' => $data['full_name'],
                'user_name' => $username,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'password' => Hash::make($data['password']),
            ]));

            $emailSent = true;
            try {
                $user->notify(new RegisterSuccessNotification);
            } catch (\Throwable $e) {
                $emailSent = false;
                report($e);
            }

            $user->load('role');

            return $this->successResponse(
                data: ['user' => $user, 'email_sent' => $emailSent],
                message: $emailSent
                    ? 'Đăng ký tài khoản thành công'
                    : 'Đăng ký tài khoản thành công, nhưng không thể gửi email'
            );
        });
    }

    private function buildUniqueUsername(string $base): string
    {
        $maxLength = 50;
        $base = substr($base, 0, $maxLength);
        $candidate = $base;
        $suffix = 1;

        while (User::query()->where('user_name', $candidate)->exists()) {
            $suffixText = '-'.$suffix;
            $candidate = substr($base, 0, max(1, $maxLength - strlen($suffixText))).$suffixText;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * Danh sách thiết bị đang đăng nhập.
     */
    public function getDevices(Request $request)
    {
        return $this->tryCatch(function () use ($request) {
            $user = $this->getAuthUser($request);
            if (! $user) {
                return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
            }

            $currentTokenId = $user->currentAccessToken()?->id;

            $tokens = $user->tokens()
                ->select(['id', 'name', 'last_used_at', 'expires_at', 'created_at'])
                ->orderByDesc('last_used_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($t) => [
                    'id' => (string) $t->id,
                    'device_name' => $t->name,
                    'last_used_at' => $t->last_used_at?->toDateTimeString(),
                    'expires_at' => $t->expires_at?->toDateTimeString(),
                    'created_at' => $t->created_at?->toDateTimeString(),
                    'is_current' => (string) $t->id === (string) $currentTokenId,
                ]);

            return $this->successResponse(data: $tokens, message: 'Danh sách thiết bị đăng nhập');
        });
    }

    /**
     * Đăng xuất thiết bị hiện tại.
     */
    public function logout(Request $request)
    {
        return $this->tryCatch(function () use ($request) {
            $user = $this->getAuthUser($request);
            if (! $user) {
                return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
            }

            $token = $user->currentAccessToken();

            if (! $token || $token instanceof TransientToken) {
                return $this->errorResponse(message: 'Bạn phải gửi Authorization: Bearer <token>', code: 401);
            }

            PersonalAccessToken::query()->where('id', $token->id)->delete();

            return $this->successResponse(data: null, message: 'Đăng xuất thành công');
        });
    }

    /**
     * Đăng xuất tất cả thiết bị.
     */
    public function logoutAll(Request $request)
    {
        return $this->tryCatch(function () use ($request) {
            $user = $this->getAuthUser($request);
            if (! $user) {
                return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
            }

            $user->tokens()->delete();

            return $this->successResponse(data: null, message: 'Đăng xuất tất cả thiết bị thành công');
        });
    }

    /**
     * Đăng xuất theo thiết bị.
     */
    public function logoutDevice(Request $request, string $tokenId)
    {
        return $this->tryCatch(function () use ($request, $tokenId) {
            $user = $this->getAuthUser($request);
            if (! $user) {
                return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
            }

            $token = $user->tokens()->where('id', $tokenId)->first();

            if (! $token) {
                return $this->errorResponse(message: 'Thiết bị không tồn tại', code: 404);
            }

            $deviceName = $token->name;
            $token->delete();

            return $this->successResponse(data: null, message: "Đăng xuất thiết bị {$deviceName} thành công");
        });
    }

    /**
     * Gửi email đặt lại mật khẩu.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $status = Password::sendResetLink(['email' => $request->validated()['email']]);

            return match ($status) {
                Password::RESET_LINK_SENT => $this->successResponse(data: null, message: 'Đã gửi mã đặt lại mật khẩu đến email của bạn'),
                Password::RESET_THROTTLED => $this->errorResponse(message: 'Vui lòng đợi ít phút trước khi gửi lại mã', code: 429),
                default => $this->errorResponse(message: 'Không thể gửi mã đặt lại mật khẩu'),
            };
        });
    }

    /**
     * Đặt lại mật khẩu.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $data = $request->validated();

            $status = Password::reset(
                ['email' => $data['email'], 'password' => $data['password'], 'token' => $data['token']],
                function ($user, $password) {
                    $user->forceFill([
                        'password' => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    $user->tokens()->delete();
                    event(new PasswordReset($user));
                }
            );

            return match ($status) {
                Password::PASSWORD_RESET => $this->successResponse(data: null, message: 'Đặt lại mật khẩu thành công'),
                Password::INVALID_TOKEN => $this->errorResponse(message: 'Mã đặt lại mật khẩu không hợp lệ hoặc đã hết hạn'),
                Password::INVALID_USER => $this->errorResponse(message: 'Email không tồn tại trong hệ thống', code: 404),
                default => $this->errorResponse(message: 'Không thể đặt lại mật khẩu'),
            };
        });
    }

    /**
     * Đổi mật khẩu.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $user = $this->getAuthUser($request);
            if (! $user) {
                return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
            }

            $data = $request->validated();

            if (! Hash::check($data['current_password'], $user->password)) {
                return $this->errorResponse(message: 'Mật khẩu hiện tại không đúng');
            }

            $user->forceFill([
                'password' => $data['password'],
                'remember_token' => Str::random(60),
            ])->save();

            $user->tokens()->delete();

            return $this->successResponse(data: null, message: 'Đổi mật khẩu thành công');
        });
    }

    /**
     * Helper: lấy user đang đăng nhập.
     */
    private function getAuthUser(Request $request)
    {
        return $request->user('sanctum');
    }
}
