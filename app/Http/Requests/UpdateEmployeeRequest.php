<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_role_id' => 'sometimes|required|uuid|exists:employee_roles,employee_role_id',
            'name' => 'sometimes|required|string|max:255',
            'hire_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after:hire_date',
            'status' => ['sometimes', 'required', Rule::in(['IN_ACTIVE', 'UN_ACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_role_id.required' => 'Vai trò nhân viên không được để trống',
            'employee_role_id.uuid' => 'ID vai trò nhân viên không hợp lệ',
            'employee_role_id.exists' => 'Vai trò nhân viên không tồn tại',

            'name.required' => 'Tên nhân viên không được để trống',

            'hire_date.required' => 'Ngày bắt đầu làm việc không được để trống',
            'hire_date.date' => 'Ngày bắt đầu làm việc không hợp lệ',

            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',

            'status.in' => 'Trạng thái không hợp lệ. Chọn: IN_ACTIVE, UN_ACTIVE',
        ];
    }
}
