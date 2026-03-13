<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_number' => 'sometimes|required|string|max:50',
            'bank_name' => 'sometimes|required|string|max:255',
            'net_salary' => 'sometimes|required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'payment_status' => ['sometimes', 'required', Rule::in(['IS_PENDING', 'IN_ACTIVE', 'UN_ACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'bank_number.required' => 'Số tài khoản không được để trống',
            'bank_name.required' => 'Tên ngân hàng không được để trống',

            'net_salary.required' => 'Lương ròng không được để trống',
            'net_salary.numeric' => 'Lương ròng phải là số',

            'bonus.numeric' => 'Thưởng phải là số',

            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ. Chọn: IS_PENDING, IN_ACTIVE, UN_ACTIVE',
        ];
    }
}
