<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\Services;

class UserServices extends Services
{
    public function __construct(
        protected User $userModel
    ) {}

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

    public function findAllUsers(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->userModel,
            request: $request,
            searchableFields: [],
            sortableFields: [],
            message: 'Lấy danh sách người dùng thành công',
        );
    }

    public function me()
    {
        return Auth::user();
    }
}
