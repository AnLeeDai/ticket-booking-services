<?php

namespace App\Services;

use App\Http\Requests\CreateShowtimeRequest;
use App\Http\Requests\UpdateShowtimeRequest;
use App\Models\Showtime;
use Illuminate\Http\Request;

class ShowtimeServices extends Services
{
    public function __construct(
        protected Showtime $showtimeModel
    ) {}

    /**
     * Lấy danh sách suất chiếu (có filter, sort, paginate).
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->showtimeModel->with([
                'cinema:cinema_id,code,name,location',
                'movie:movie_id,code,name,title,thumb_url',
            ]),
            request: $request,
            sortableFields: ['starts_at', 'ends_at', 'screen_type', 'created_at'],
            message: 'Lấy danh sách suất chiếu thành công',
        );
    }

    /**
     * Lấy chi tiết suất chiếu theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->showtimeModel,
            id: $id,
            relations: [
                'cinema:cinema_id,code,name,location',
                'movie:movie_id,code,name,title,thumb_url',
            ],
            message: 'Lấy thông tin suất chiếu thành công',
            notFoundMessage: 'Không tìm thấy suất chiếu',
        );
    }

    /**
     * Tạo suất chiếu mới.
     */
    public function store(CreateShowtimeRequest $request)
    {
        return $this->createRecord(
            model: $this->showtimeModel,
            data: $request->validated(),
            message: 'Tạo suất chiếu thành công',
            failMessage: 'Tạo suất chiếu thất bại',
        );
    }

    /**
     * Cập nhật suất chiếu theo ID.
     */
    public function update(UpdateShowtimeRequest $request, string $id)
    {
        return $this->updateRecord(
            model: $this->showtimeModel,
            id: $id,
            data: array_filter($request->validated(), fn ($v) => ! is_null($v)),
            message: 'Cập nhật suất chiếu thành công',
            notFoundMessage: 'Không tìm thấy suất chiếu',
        );
    }

    /**
     * Xoá suất chiếu theo ID.
     */
    public function destroy(string $id)
    {
        return $this->deleteRecord(
            model: $this->showtimeModel,
            id: $id,
            message: 'Xoá suất chiếu thành công',
            notFoundMessage: 'Không tìm thấy suất chiếu',
        );
    }
}
