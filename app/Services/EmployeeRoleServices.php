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
        return $this->deleteRecord(
            model: $this->employeeRoleModel,
            id: $id,
            message: 'Xoá vai trò nhân viên thành công',
            notFoundMessage: 'Không tìm thấy vai trò nhân viên',
        );
    }
}
