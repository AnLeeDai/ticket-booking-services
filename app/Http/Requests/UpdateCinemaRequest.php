<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCinemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:500',
            'active' => ['sometimes', 'required', Rule::in(['IN_ACTIVE', 'UN_ACTIVE'])],
            'manager_id' => 'nullable|uuid|exists:users,user_id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Tên rạp không được vượt quá 255 ký tự',
            'location.max' => 'Địa chỉ rạp không được vượt quá 500 ký tự',
            'active.in' => 'Trạng thái không hợp lệ. Chọn: IN_ACTIVE, UN_ACTIVE',
            'manager_id.uuid' => 'ID quản lý không hợp lệ',
            'manager_id.exists' => 'Người quản lý không tồn tại',
        ];
    }
}
