<?php

namespace App\Services;

use App\Models\User;

class UserServices extends Services
{
    public function __construct(
        protected User $userModel
    ) {}

    public function createToken(User $user, string $deviceName = 'api')
    {
        try {
            $user->tokens()->where('name', $deviceName)->delete();

            $token = $user->createToken($deviceName)->plainTextToken;

            if (! $token) {
                return $this->errorResponse('Không thể tạo token cho người dùng', 500);
            }

            return [
                'token_type' => 'Bearer',
                'access_token' => $token,
            ];
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }
}
