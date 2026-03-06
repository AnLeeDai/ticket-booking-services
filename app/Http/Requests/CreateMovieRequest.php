<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_ids'   => 'required|array|min:1',
            'category_ids.*' => 'uuid|exists:categories,id',
            'title'          => 'required|string|max:255',
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'thumb_url'      => 'required|string|max:500',
            'trailer_url'    => 'required|string|max:500',
            'gallery'        => 'nullable|array',
            'gallery.*'      => 'string|max:500',
            'duration'       => 'required|integer|min:1',
            'language'       => 'required|string|max:100',
            'age'            => 'required|integer|min:0|max:255',
            'rating'         => 'nullable|numeric|min:0|max:9.9',
            'release_date'   => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:release_date',
            'status'         => ['required', Rule::in(['IN_ACTIVE', 'UN_ACTIVE', 'IS_PENDING'])],
        ];
    }

    public function messages(): array
    {
        return [
            'category_ids.required'   => 'Thể loại phim không được để trống',
            'category_ids.array'      => 'Thể loại phim phải là mảng',
            'category_ids.min'        => 'Phải chọn ít nhất 1 thể loại',
            'category_ids.*.uuid'     => 'ID thể loại không hợp lệ',
            'category_ids.*.exists'   => 'Một hoặc nhiều thể loại không tồn tại',

            'title.required'        => 'Tiêu đề phim không được để trống',
            'title.max'             => 'Tiêu đề phim không được vượt quá 255 ký tự',

            'name.required'         => 'Tên phim không được để trống',
            'name.max'              => 'Tên phim không được vượt quá 255 ký tự',

            'thumb_url.required'    => 'Ảnh thumbnail không được để trống',
            'trailer_url.required'  => 'URL trailer không được để trống',
            'gallery.array'         => 'Gallery phải là mảng URL',
            'gallery.*.string'      => 'Mỗi ảnh trong gallery phải là chuỗi URL',
            'gallery.*.max'         => 'URL ảnh gallery không được vượt quá 500 ký tự',

            'duration.required'     => 'Thời lượng phim không được để trống',
            'duration.integer'      => 'Thời lượng phải là số nguyên (phút)',
            'duration.min'          => 'Thời lượng phải lớn hơn 0',

            'language.required'     => 'Ngôn ngữ không được để trống',

            'age.required'          => 'Giới hạn độ tuổi không được để trống',
            'age.integer'           => 'Giới hạn độ tuổi phải là số nguyên',
            'age.min'               => 'Giới hạn độ tuổi không được âm',

            'rating.numeric'        => 'Điểm đánh giá phải là số',
            'rating.min'            => 'Điểm đánh giá tối thiểu là 0',
            'rating.max'            => 'Điểm đánh giá tối đa là 9.9',

            'release_date.required' => 'Ngày khởi chiếu không được để trống',
            'release_date.date'     => 'Ngày khởi chiếu không hợp lệ',

            'end_date.date'             => 'Ngày kết thúc không hợp lệ',
            'end_date.after_or_equal'   => 'Ngày kết thúc phải sau hoặc bằng ngày khởi chiếu',

            'status.required'       => 'Trạng thái phim không được để trống',
            'status.in'             => 'Trạng thái không hợp lệ. Chọn: IN_ACTIVE, UN_ACTIVE, IS_PENDING',
        ];
    }
}
