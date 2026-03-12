<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateShowtimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cinema_id' => 'required|uuid|exists:cinemas,cinema_id',
            'movie_id' => 'required|uuid|exists:movies,movie_id',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'screen_type' => ['required', Rule::in(['2D', '3D'])],
        ];
    }

    public function messages(): array
    {
        return [
            'cinema_id.required' => 'Rạp chiếu không được để trống',
            'cinema_id.uuid' => 'ID rạp chiếu không hợp lệ',
            'cinema_id.exists' => 'Rạp chiếu không tồn tại',

            'movie_id.required' => 'Phim không được để trống',
            'movie_id.uuid' => 'ID phim không hợp lệ',
            'movie_id.exists' => 'Phim không tồn tại',

            'starts_at.required' => 'Thời gian bắt đầu không được để trống',
            'starts_at.date' => 'Thời gian bắt đầu không hợp lệ',
            'starts_at.after' => 'Thời gian bắt đầu phải sau thời điểm hiện tại',

            'ends_at.required' => 'Thời gian kết thúc không được để trống',
            'ends_at.date' => 'Thời gian kết thúc không hợp lệ',
            'ends_at.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',

            'screen_type.required' => 'Loại màn hình không được để trống',
            'screen_type.in' => 'Loại màn hình không hợp lệ. Chọn: 2D, 3D',
        ];
    }
}
