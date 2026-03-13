<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'showtime_id' => 'required|uuid|exists:showtimes,showtime_id',
            'seat_code' => [
                'required',
                'string',
                Rule::unique('seats')->where(function ($query) {
                    return $query->where('showtime_id', $this->showtime_id);
                }),
            ],
            'seat_type' => ['required', Rule::in(['VIP', 'COUPLE', 'NORMAL'])],
            'price' => 'required|numeric|min:0',
            'active' => ['nullable', Rule::in(['IN_ACTIVE', 'UN_ACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'showtime_id.required' => 'Suất chiếu không được để trống',
            'showtime_id.uuid' => 'ID suất chiếu không hợp lệ',
            'showtime_id.exists' => 'Suất chiếu không tồn tại',

            'seat_code.required' => 'Mã ghế không được để trống',
            'seat_code.unique' => 'Mã ghế đã tồn tại trong suất chiếu này',

            'seat_type.required' => 'Loại ghế không được để trống',
            'seat_type.in' => 'Loại ghế không hợp lệ. Chọn: VIP, COUPLE, NORMAL',

            'price.required' => 'Giá ghế không được để trống',
            'price.numeric' => 'Giá ghế phải là số',
            'price.min' => 'Giá ghế không được âm',

            'active.in' => 'Trạng thái không hợp lệ. Chọn: IN_ACTIVE, UN_ACTIVE',
        ];
    }
}
