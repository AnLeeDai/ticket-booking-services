<?php

namespace App\Services;

use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeServices extends Services
{
    public function __construct(
        protected Employee $employeeModel
    ) {}

    /**
     * Lấy danh sách nhân viên.
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->employeeModel->with([
                'employeeRole:employee_role_id,name',
                'user:user_id,full_name,email,phone',
            ]),
            request: $request,
            searchableFields: ['name', 'code'],
            sortableFields: ['name', 'code', 'hire_date', 'status', 'created_at'],
            message: 'Lấy danh sách nhân viên thành công',
        );
    }

    /**
     * Lấy chi tiết nhân viên theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->employeeModel,
            id: $id,
            relations: [
                'employeeRole:employee_role_id,name',
                'user:user_id,full_name,email,phone',
                'salary',
            ],
            message: 'Lấy thông tin nhân viên thành công',
            notFoundMessage: 'Không tìm thấy nhân viên',
        );
    }

    /**
     * Tạo nhân viên mới (code tự động sinh).
     */
    public function store(CreateEmployeeRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $data = $request->validated();

            $employee = $this->employeeModel->create(array_merge($data, [
                'code' => $this->generateCode(),
            ]));

            return $this->successResponse(
                data: $employee->load([
                    'employeeRole:employee_role_id,name',
                    'user:user_id,full_name,email,phone',
                ]),
                message: 'Tạo nhân viên thành công',
            );
        });
    }

    /**
     * Cập nhật nhân viên theo ID.
     */
    public function update(UpdateEmployeeRequest $request, string $id)
    {
        return $this->updateRecord(
            model: $this->employeeModel,
            id: $id,
            data: array_filter($request->validated(), fn ($v) => ! is_null($v)),
            message: 'Cập nhật nhân viên thành công',
            notFoundMessage: 'Không tìm thấy nhân viên',
        );
    }

    /**
     * Xoá nhân viên theo ID.
     */
    public function destroy(string $id)
    {
        return $this->deleteRecord(
            model: $this->employeeModel,
            id: $id,
            message: 'Xoá nhân viên thành công',
            notFoundMessage: 'Không tìm thấy nhân viên',
        );
    }

    /**
     * Tự sinh mã nhân viên theo format: EMP-XXXXXX
     */
    private function generateCode(): string
    {
        $count = $this->employeeModel->count() + 1;
        $code = sprintf('EMP-%06d', $count);

        while ($this->employeeModel->where('code', $code)->exists()) {
            $count++;
            $code = sprintf('EMP-%06d', $count);
        }

        return $code;
    }
}
