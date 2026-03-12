<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,employee_id|unique:employee_salaries,employee_id',
            'bank_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'net_salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'total_earn' => 'required|numeric|min:0',
            'payment_status' => ['nullable', Rule::in(['IS_PENDING', 'IN_ACTIVE', 'UN_ACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Nhân viên không được để trống',
            'employee_id.uuid' => 'ID nhân viên không hợp lệ',
            'employee_id.exists' => 'Nhân viên không tồn tại',
            'employee_id.unique' => 'Nhân viên đã có bảng lương',

            'bank_number.required' => 'Số tài khoản không được để trống',
            'bank_name.required' => 'Tên ngân hàng không được để trống',

            'net_salary.required' => 'Lương ròng không được để trống',
            'net_salary.numeric' => 'Lương ròng phải là số',

            'bonus.numeric' => 'Thưởng phải là số',

            'total_earn.required' => 'Tổng thu nhập không được để trống',
            'total_earn.numeric' => 'Tổng thu nhập phải là số',

            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ. Chọn: IS_PENDING, IN_ACTIVE, UN_ACTIVE',
        ];
    }
}
