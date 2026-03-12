<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'showtime_id' => 'required|uuid|exists:showtimes,showtime_id',
            'seat_id' => 'required|uuid|exists:seats,seat_id',
            'payment_method' => ['required', Rule::in(['TRANSFER', 'CARD', 'CASH'])],
            'combos' => 'nullable|array',
            'combos.*.combo_id' => 'required_with:combos|uuid|exists:combos,combo_id',
            'combos.*.qty' => 'required_with:combos|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'showtime_id.required' => 'Suất chiếu không được để trống',
            'showtime_id.uuid' => 'ID suất chiếu không hợp lệ',
            'showtime_id.exists' => 'Suất chiếu không tồn tại',

            'seat_id.required' => 'Ghế không được để trống',
            'seat_id.uuid' => 'ID ghế không hợp lệ',
            'seat_id.exists' => 'Ghế không tồn tại',

            'payment_method.required' => 'Phương thức thanh toán không được để trống',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ. Chọn: TRANSFER, CARD, CASH',

            'combos.array' => 'Danh sách combo phải là mảng',
            'combos.*.combo_id.required_with' => 'ID combo không được để trống',
            'combos.*.combo_id.uuid' => 'ID combo không hợp lệ',
            'combos.*.combo_id.exists' => 'Combo không tồn tại',
            'combos.*.qty.required_with' => 'Số lượng combo không được để trống',
            'combos.*.qty.integer' => 'Số lượng combo phải là số nguyên',
            'combos.*.qty.min' => 'Số lượng combo tối thiểu là 1',
        ];
    }
}
