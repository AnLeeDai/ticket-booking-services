<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserServices extends Services
{
    public function __construct(
        protected User $userModel
    ) {}

    /**
     * Tạo token cho user (xoá token cũ cùng device).
     */
    public function createToken(User $user, string $deviceName = 'api'): array
    {
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'role' => $user->role?->name,
        ];
    }

    /**
     * Lấy danh sách người dùng.
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->userModel,
            request: $request,
            searchableFields: ['full_name', 'email', 'user_name', 'phone'],
            sortableFields: ['full_name', 'email', 'created_at'],
            message: 'Lấy danh sách người dùng thành công',
        );
    }

    /**
     * Lấy thông tin user đang đăng nhập.
     */
    public function getProfile()
    {
        $user = Auth::user();

        if (! $user) {
            return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
        }

        return $this->successResponse(data: $user, message: 'Lấy thông tin cá nhân thành công');
    }
}
