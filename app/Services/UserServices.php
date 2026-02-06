<?php

namespace App\Services;

use App\Models\User;

class UserServices extends Services
{
    public function __construct(
        protected User $userModel
    ) {}

    /**
     * Tạo token cho user, xoá token cũ cùng thiết bị.
     */
    public function createToken(User $user, string $deviceName = 'api')
    {
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        if (! $token) {
            return $this->errorResponse('Không thể tạo token cho người dùng', 500);
        }

        return [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'role' => $user->role?->name,
        ];
    }
}
