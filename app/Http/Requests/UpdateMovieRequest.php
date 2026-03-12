<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $movieId = $this->route('id');

        return [
            'gender_id' => 'nullable|uuid|exists:categories,id',
            'category_ids' => 'sometimes|required|array|min:1',
            'category_ids.*' => 'uuid|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'thumb_url' => 'sometimes|required|string|max:500',
            'trailer_url' => 'sometimes|required|string|max:500',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string|max:500',
            'duration' => 'sometimes|required|integer|min:1',
            'language' => 'sometimes|required|string|max:100',
            'age' => 'sometimes|required|integer|min:0|max:255',
            'rating' => 'nullable|numeric|min:0|max:9.9',
            'release_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:release_date',
            'status' => ['sometimes', 'required', Rule::in(['IN_ACTIVE', 'UN_ACTIVE', 'IS_PENDING'])],
        ];
    }

    public function messages(): array
    {
        return [
            'gender_id.uuid' => 'ID thể loại chính không hợp lệ',
            'gender_id.exists' => 'Thể loại chính không tồn tại',

            'category_ids.array' => 'Thể loại phim phải là mảng',
            'category_ids.min' => 'Phải chọn ít nhất 1 thể loại',
            'category_ids.*.uuid' => 'ID thể loại không hợp lệ',
            'category_ids.*.exists' => 'Một hoặc nhiều thể loại không tồn tại',

            'title.max' => 'Tiêu đề phim không được vượt quá 255 ký tự',
            'name.max' => 'Tên phim không được vượt quá 255 ký tự',
            'gallery.array' => 'Gallery phải là mảng URL',
            'gallery.*.string' => 'Mỗi ảnh trong gallery phải là chuỗi URL',
            'gallery.*.max' => 'URL ảnh gallery không được vượt quá 500 ký tự',

            'duration.integer' => 'Thời lượng phải là số nguyên (phút)',
            'duration.min' => 'Thời lượng phải lớn hơn 0',

            'age.integer' => 'Giới hạn độ tuổi phải là số nguyên',
            'age.min' => 'Giới hạn độ tuổi không được âm',

            'rating.numeric' => 'Điểm đánh giá phải là số',
            'rating.min' => 'Điểm đánh giá tối thiểu là 0',
            'rating.max' => 'Điểm đánh giá tối đa là 9.9',

            'release_date.date' => 'Ngày khởi chiếu không hợp lệ',
            'end_date.date' => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày khởi chiếu',

            'status.in' => 'Trạng thái không hợp lệ. Chọn: IN_ACTIVE, UN_ACTIVE, IS_PENDING',
        ];
    }
}
