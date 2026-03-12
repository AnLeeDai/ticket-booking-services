<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_role_id' => 'required|uuid|exists:employee_roles,employee_role_id',
            'user_id' => 'required|uuid|exists:users,user_id|unique:employees,user_id',
            'name' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'end_date' => 'nullable|date|after:hire_date',
            'status' => ['nullable', Rule::in(['IN_ACTIVE', 'UN_ACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_role_id.required' => 'Vai trò nhân viên không được để trống',
            'employee_role_id.uuid' => 'ID vai trò nhân viên không hợp lệ',
            'employee_role_id.exists' => 'Vai trò nhân viên không tồn tại',

            'user_id.required' => 'Tài khoản không được để trống',
            'user_id.uuid' => 'ID tài khoản không hợp lệ',
            'user_id.exists' => 'Tài khoản không tồn tại',
            'user_id.unique' => 'Tài khoản đã được gán cho nhân viên khác',

            'name.required' => 'Tên nhân viên không được để trống',
            'name.string' => 'Tên nhân viên phải là chuỗi ký tự',

            'hire_date.required' => 'Ngày bắt đầu làm việc không được để trống',
            'hire_date.date' => 'Ngày bắt đầu làm việc không hợp lệ',

            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',

            'status.in' => 'Trạng thái không hợp lệ. Chọn: IN_ACTIVE, UN_ACTIVE',
        ];
    }
}
