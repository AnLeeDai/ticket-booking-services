<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', Rule::in(['STAFF', 'PROBATION'])],
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên vai trò không được để trống',
            'name.in' => 'Tên vai trò không hợp lệ. Chọn: STAFF, PROBATION',
            'description.string' => 'Mô tả phải là chuỗi ký tự',
        ];
    }
}
