<?php

namespace App\Services;

use App\Http\Requests\CreateEmployeeRoleRequest;
use App\Http\Requests\UpdateEmployeeRoleRequest;
use App\Models\EmployeeRole;
use Illuminate\Http\Request;

class EmployeeRoleServices extends Services
{
    public function __construct(
        protected EmployeeRole $employeeRoleModel
    ) {}

    /**
     * Lấy danh sách vai trò nhân viên.
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->employeeRoleModel,
            request: $request,
            searchableFields: ['name', 'description'],
            sortableFields: ['name', 'created_at'],
            message: 'Lấy danh sách vai trò nhân viên thành công',
        );
    }

    /**
     * Lấy chi tiết vai trò nhân viên theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->employeeRoleModel,
            id: $id,
            message: 'Lấy thông tin vai trò nhân viên thành công',
            notFoundMessage: 'Không tìm thấy vai trò nhân viên',
        );
    }

    /**
     * Tạo vai trò nhân viên mới.
     */
    public function store(CreateEmployeeRoleRequest $request)
    {
        return $this->createRecord(
            model: $this->employeeRoleModel,
            data: $request->validated(),
            message: 'Tạo vai trò nhân viên thành công',
            failMessage: 'Tạo vai trò nhân viên thất bại',
        );
    }

    /**
     * Cập nhật vai trò nhân viên theo ID.
     */
    public function update(UpdateEmployeeRoleRequest $request, string $id)
    {
        return $this->updateRecord(
            model: $this->employeeRoleModel,
            id: $id,
            data: array_filter($request->validated(), fn ($v) => ! is_null($v)),
            message: 'Cập nhật vai trò nhân viên thành công',
            notFoundMessage: 'Không tìm thấy vai trò nhân viên',
        );
    }

    /**
     * Xoá vai trò nhân viên theo ID.
     */
    public function destroy(string $id)
    {
        return $this->tryCatch(function () use ($id) {
            $role = $this->employeeRoleModel->find($id);

            if (! $role) {
                return $this->errorResponse(message: 'Không tìm thấy vai trò nhân viên', code: 404);
            }

            $employeeCount = $role->employees()->count();
            if ($employeeCount > 0) {
                return $this->errorResponse(
                    message: "Không thể xoá vai trò đang được gán cho {$employeeCount} nhân viên",
                );
            }

            $role->delete();

            return $this->successResponse(data: null, message: 'Xoá vai trò nhân viên thành công');
        });
    }
}
