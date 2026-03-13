<?php

namespace App\Services;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
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

    /**
     * Cập nhật thông tin cá nhân.
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $user = Auth::user();

            if (! $user) {
                return $this->errorResponse(message: 'Chưa đăng nhập', code: 401);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

            $user->update($data);

            return $this->successResponse(data: $user->fresh(), message: 'Cập nhật thông tin cá nhân thành công');
        });
    }

    /**
     * Admin cập nhật thông tin user.
     */
    public function updateUser(UpdateUserRequest $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $user = $this->userModel->find($id);

            if (! $user) {
                return $this->errorResponse(message: 'Không tìm thấy người dùng', code: 404);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

            $user->update($data);

            return $this->successResponse(data: $user->fresh(), message: 'Cập nhật người dùng thành công');
        });
    }

    /**
     * Admin xoá user (soft delete).
     */
    public function deleteUser(string $id)
    {
        return $this->tryCatch(function () use ($id) {
            $user = $this->userModel->find($id);

            if (! $user) {
                return $this->errorResponse(message: 'Không tìm thấy người dùng', code: 404);
            }

            if ($user->role?->name === 'admin') {
                return $this->errorResponse(message: 'Không thể xoá tài khoản admin');
            }

            // Check if user is an active employee
            $activeEmployee = $user->employee()->whereNull('deleted_at')->first();
            if ($activeEmployee) {
                return $this->errorResponse(message: 'Không thể xoá người dùng đang là nhân viên. Vui lòng xoá nhân viên trước');
            }

            // Check if user has active tickets
            $activeTickets = $user->tickets()->whereIn('status', ['IS_PENDING', 'IN_ACTIVE'])->count();
            if ($activeTickets > 0) {
                return $this->errorResponse(
                    message: "Không thể xoá người dùng đang có {$activeTickets} vé chưa xử lý",
                );
            }

            $user->tokens()->delete();
            $user->delete();

            return $this->successResponse(data: null, message: 'Xoá người dùng thành công');
        });
    }
}
