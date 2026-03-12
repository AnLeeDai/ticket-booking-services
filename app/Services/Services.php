<?php

namespace App\Services;

use App\Traits\JsonResponse;
use App\Traits\QueryFilter;
use Illuminate\Http\Request;

abstract class Services
{
    use JsonResponse, QueryFilter;

    public function __construct() {}

    /**
     * Lấy role name của user hiện tại.
     */
    protected function getUserRole(Request $request): ?string
    {
        return $request->user()?->role?->name;
    }

    /**
     * Kiểm tra user có phải admin.
     */
    protected function isAdmin(Request $request): bool
    {
        return $this->getUserRole($request) === 'admin';
    }

    /**
     * Kiểm tra user có phải manager.
     */
    protected function isManager(Request $request): bool
    {
        return $this->getUserRole($request) === 'manager';
    }

    /**
     * Lấy danh sách cinema_id mà user có quyền quản lý.
     * Admin: null (toàn quyền, không cần filter).
     * Manager: cinemas mà user quản lý.
     * Employee: cinema nơi nhân viên làm việc.
     */
    protected function getManagedCinemaIds(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return [];
        }

        $role = $user->role?->name;

        if ($role === 'admin') {
            return null;
        }

        if ($role === 'manager') {
            return $user->managedCinemas()->pluck('cinema_id')->toArray();
        }

        if ($role === 'employee') {
            $employee = $user->employee;

            return $employee?->cinema_id ? [$employee->cinema_id] : [];
        }

        return [];
    }

    /**
     * Kiểm tra user có quyền truy cập cinema.
     */
    protected function canAccessCinema(Request $request, ?string $cinemaId): bool
    {
        if (! $cinemaId) {
            return false;
        }

        $cinemaIds = $this->getManagedCinemaIds($request);

        if ($cinemaIds === null) {
            return true;
        }

        return in_array($cinemaId, $cinemaIds);
    }
}
