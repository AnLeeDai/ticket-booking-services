<?php

namespace App\Services;

use App\Http\Requests\CreateEmployeeSalaryRequest;
use App\Http\Requests\UpdateEmployeeSalaryRequest;
use App\Models\Employee;
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
        $query = $this->employeeSalaryModel->with([
            'employee:employee_id,name,code,cinema_id',
            'employee.cinema:cinema_id,code,name',
        ]);

        $cinemaIds = $this->getManagedCinemaIds($request);
        if ($cinemaIds !== null) {
            $query->whereHas('employee', fn ($q) => $q->whereIn('cinema_id', $cinemaIds));
        }

        return $this->filterAndPaginate(
            query: $query,
            request: $request,
            searchableFields: ['bank_name', 'bank_number'],
            sortableFields: ['net_salary', 'total_earn', 'payment_status', 'created_at'],
            message: 'Lấy danh sách bảng lương thành công',
        );
    }

    /**
     * Lấy chi tiết bảng lương theo ID.
     */
    public function getById(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $salary = $this->employeeSalaryModel->with([
                'employee:employee_id,name,code,cinema_id',
                'employee.cinema:cinema_id,code,name',
            ])->find($id);

            if (! $salary) {
                return $this->errorResponse(message: 'Không tìm thấy bảng lương', code: 404);
            }

            if (! $this->canAccessCinema($request, $salary->employee?->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xem bảng lương này', code: 403);
            }

            return $this->successResponse(data: $salary, message: 'Lấy thông tin bảng lương thành công');
        });
    }

    /**
     * Tạo bảng lương mới.
     */
    public function store(CreateEmployeeSalaryRequest $request)
    {
        $data = $request->validated();
        $data['total_earn'] = ($data['net_salary'] ?? 0) + ($data['bonus'] ?? 0);

        $employee = Employee::find($data['employee_id']);

        if ($employee && ! $this->canAccessCinema($request, $employee->cinema_id)) {
            return $this->errorResponse(message: 'Không có quyền tạo bảng lương cho nhân viên này', code: 403);
        }

        return $this->createRecord(
            model: $this->employeeSalaryModel,
            data: $data,
            message: 'Tạo bảng lương thành công',
            failMessage: 'Tạo bảng lương thất bại',
        );
    }

    /**
     * Cập nhật bảng lương theo ID.
     */
    public function update(UpdateEmployeeSalaryRequest $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $salary = $this->employeeSalaryModel->with('employee:employee_id,cinema_id')->find($id);

            if (! $salary) {
                return $this->errorResponse(message: 'Không tìm thấy bảng lương', code: 404);
            }

            if (! $this->canAccessCinema($request, $salary->employee?->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền cập nhật bảng lương này', code: 403);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

            if (isset($data['net_salary']) || isset($data['bonus'])) {
                $netSalary = $data['net_salary'] ?? $salary->net_salary;
                $bonus = $data['bonus'] ?? $salary->bonus ?? 0;
                $data['total_earn'] = $netSalary + $bonus;
            }

            $salary->update($data);

            return $this->successResponse(data: $salary->fresh(), message: 'Cập nhật bảng lương thành công');
        });
    }

    /**
     * Xoá bảng lương theo ID.
     */
    public function destroy(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $salary = $this->employeeSalaryModel->with('employee:employee_id,cinema_id')->find($id);

            if (! $salary) {
                return $this->errorResponse(message: 'Không tìm thấy bảng lương', code: 404);
            }

            if (! $this->canAccessCinema($request, $salary->employee?->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xoá bảng lương này', code: 403);
            }

            $salary->delete();

            return $this->successResponse(data: null, message: 'Xoá bảng lương thành công');
        });
    }
}
