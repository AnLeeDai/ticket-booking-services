<?php

namespace App\Services;

use App\Http\Requests\CreateEmployeeSalaryRequest;
use App\Http\Requests\UpdateEmployeeSalaryRequest;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;

class EmployeeSalaryServices extends Services
{
    public function __construct(
        protected EmployeeSalary $employeeSalaryModel
    ) {}

    /**
     * Lấy danh sách bảng lương.
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->employeeSalaryModel->with([
                'employee:employee_id,name,code',
            ]),
            request: $request,
            searchableFields: ['bank_name', 'bank_number'],
            sortableFields: ['net_salary', 'total_earn', 'payment_status', 'created_at'],
            message: 'Lấy danh sách bảng lương thành công',
        );
    }

    /**
     * Lấy chi tiết bảng lương theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->employeeSalaryModel,
            id: $id,
            relations: ['employee:employee_id,name,code'],
            message: 'Lấy thông tin bảng lương thành công',
            notFoundMessage: 'Không tìm thấy bảng lương',
        );
    }

    /**
     * Tạo bảng lương mới.
     */
    public function store(CreateEmployeeSalaryRequest $request)
    {
        return $this->createRecord(
            model: $this->employeeSalaryModel,
            data: $request->validated(),
            message: 'Tạo bảng lương thành công',
            failMessage: 'Tạo bảng lương thất bại',
        );
    }

    /**
     * Cập nhật bảng lương theo ID.
     */
    public function update(UpdateEmployeeSalaryRequest $request, string $id)
    {
        return $this->updateRecord(
            model: $this->employeeSalaryModel,
            id: $id,
            data: array_filter($request->validated(), fn ($v) => ! is_null($v)),
            message: 'Cập nhật bảng lương thành công',
            notFoundMessage: 'Không tìm thấy bảng lương',
        );
    }

    /**
     * Xoá bảng lương theo ID.
     */
    public function destroy(string $id)
    {
        return $this->deleteRecord(
            model: $this->employeeSalaryModel,
            id: $id,
            message: 'Xoá bảng lương thành công',
            notFoundMessage: 'Không tìm thấy bảng lương',
        );
    }
}
