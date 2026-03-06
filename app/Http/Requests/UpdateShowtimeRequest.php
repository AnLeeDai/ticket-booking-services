<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShowtimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cinema_id' => 'sometimes|required|uuid|exists:cinemas,cinema_id',
            'movie_id' => 'sometimes|required|uuid|exists:movies,movie_id',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'sometimes|required|date|after:starts_at',
            'screen_type' => ['sometimes', 'required', Rule::in(['2D', '3D'])],
        ];
    }

    public function messages(): array
    {
        return [
            'cinema_id.uuid' => 'ID rạp chiếu không hợp lệ',
            'cinema_id.exists' => 'Rạp chiếu không tồn tại',

            'movie_id.uuid' => 'ID phim không hợp lệ',
            'movie_id.exists' => 'Phim không tồn tại',

            'starts_at.date' => 'Thời gian bắt đầu không hợp lệ',
            'ends_at.date' => 'Thời gian kết thúc không hợp lệ',
            'ends_at.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',

            'screen_type.in' => 'Loại màn hình không hợp lệ. Chọn: 2D, 3D',
        ];
    }
}
