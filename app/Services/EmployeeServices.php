<?php

namespace App\Services;

use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $query = $this->employeeModel->with([
            'employeeRole:employee_role_id,name',
            'user:user_id,full_name,email,phone',
            'cinema:cinema_id,code,name',
        ]);

        $cinemaIds = $this->getManagedCinemaIds($request);
        if ($cinemaIds !== null) {
            $query->whereIn('cinema_id', $cinemaIds);
        }

        return $this->filterAndPaginate(
            query: $query,
            request: $request,
            searchableFields: ['name', 'code'],
            sortableFields: ['name', 'code', 'hire_date', 'status', 'created_at'],
            message: 'Lấy danh sách nhân viên thành công',
        );
    }

    /**
     * Lấy chi tiết nhân viên theo ID.
     */
    public function getById(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $employee = $this->employeeModel->with([
                'employeeRole:employee_role_id,name',
                'user:user_id,full_name,email,phone',
                'salary',
                'cinema:cinema_id,code,name',
            ])->find($id);

            if (! $employee) {
                return $this->errorResponse(message: 'Không tìm thấy nhân viên', code: 404);
            }

            if (! $this->canAccessCinema($request, $employee->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xem nhân viên này', code: 403);
            }

            return $this->successResponse(data: $employee, message: 'Lấy thông tin nhân viên thành công');
        });
    }

    /**
     * Tạo nhân viên mới (code tự động sinh).
     */
    public function store(CreateEmployeeRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $data = $request->validated();

            if (! $this->canAccessCinema($request, $data['cinema_id'])) {
                return $this->errorResponse(message: 'Không có quyền thêm nhân viên cho rạp này', code: 403);
            }

            $employee = DB::transaction(function () use ($data) {
                $employee = $this->employeeModel->create(array_merge($data, [
                    'code' => $this->generateCode(),
                ]));

                // Cập nhật role user thành 'employee' nếu user hiện tại là customer
                $user = $employee->user;
                if ($user && $user->role?->name === 'customer') {
                    $employeeRoleId = Role::where('name', 'employee')->value('role_id');
                    if ($employeeRoleId) {
                        $user->update(['role_id' => $employeeRoleId]);
                    }
                }

                return $employee;
            });

            return $this->successResponse(
                data: $employee->load([
                    'employeeRole:employee_role_id,name',
                    'user:user_id,full_name,email,phone',
                    'cinema:cinema_id,code,name',
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
        return $this->tryCatch(function () use ($request, $id) {
            $employee = $this->employeeModel->find($id);

            if (! $employee) {
                return $this->errorResponse(message: 'Không tìm thấy nhân viên', code: 404);
            }

            if (! $this->canAccessCinema($request, $employee->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền cập nhật nhân viên này', code: 403);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

            if (isset($data['cinema_id']) && ! $this->canAccessCinema($request, $data['cinema_id'])) {
                return $this->errorResponse(message: 'Không có quyền chuyển nhân viên sang rạp này', code: 403);
            }

            $employee->update($data);

            return $this->successResponse(data: $employee->fresh(), message: 'Cập nhật nhân viên thành công');
        });
    }

    /**
     * Xoá nhân viên theo ID.
     */
    public function destroy(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $employee = $this->employeeModel->find($id);

            if (! $employee) {
                return $this->errorResponse(message: 'Không tìm thấy nhân viên', code: 404);
            }

            if (! $this->canAccessCinema($request, $employee->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xoá nhân viên này', code: 403);
            }

            DB::transaction(function () use ($employee) {
                // Clean up salary record if exists
                $employee->salary?->delete();
                $employee->delete();

                // Reset role user về 'customer' nếu user không còn là nhân viên ở đâu
                $user = $employee->user;
                if ($user && $user->role?->name === 'employee') {
                    $stillEmployee = $this->employeeModel
                        ->where('user_id', $user->user_id)
                        ->where('employee_id', '!=', $employee->employee_id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if (! $stillEmployee) {
                        $customerRoleId = Role::where('name', 'customer')->value('role_id');
                        if ($customerRoleId) {
                            $user->update(['role_id' => $customerRoleId]);
                        }
                    }
                }
            });

            return $this->successResponse(data: null, message: 'Xoá nhân viên thành công');
        });
    }

    /**
     * Tự sinh mã nhân viên theo format: EMP-XXXXXX
     */
    private function generateCode(): string
    {
        $count = $this->employeeModel->withTrashed()->count() + 1;
        $code = sprintf('EMP-%06d', $count);

        while ($this->employeeModel->withTrashed()->where('code', $code)->exists()) {
            $count++;
            $code = sprintf('EMP-%06d', $count);
        }

        return $code;
    }
}
