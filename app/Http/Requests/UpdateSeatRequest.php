<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seat_code' => 'sometimes|required|string',
            'seat_type' => ['sometimes', 'required', Rule::in(['VIP', 'COUPLE', 'NORMAL'])],
            'price' => 'sometimes|required|numeric|min:0',
            'active' => ['sometimes', 'required', Rule::in(['IN_ACTIVE', 'UN_ACTIVE', 'HOLD', 'SOLD'])],
            'hold_until' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'seat_code.required' => 'Mã ghế không được để trống',

            'seat_type.required' => 'Loại ghế không được để trống',
            'seat_type.in' => 'Loại ghế không hợp lệ. Chọn: VIP, COUPLE, NORMAL',

            'price.required' => 'Giá ghế không được để trống',
            'price.numeric' => 'Giá ghế phải là số',
            'price.min' => 'Giá ghế không được âm',

            'active.in' => 'Trạng thái không hợp lệ. Chọn: IN_ACTIVE, UN_ACTIVE, HOLD, SOLD',

            'hold_until.date' => 'Thời gian giữ ghế không hợp lệ',
        ];
    }
}
