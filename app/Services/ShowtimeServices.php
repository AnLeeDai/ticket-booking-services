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
            filterableFields: ['cinema_id', 'movie_id', 'screen_type'],
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
        $data = $request->validated();

        if (! $this->canAccessCinema($request, $data['cinema_id'])) {
            return $this->errorResponse(message: 'Không có quyền tạo suất chiếu cho rạp này', code: 403);
        }

        if ($this->hasOverlap($data['cinema_id'], $data['starts_at'], $data['ends_at'])) {
            return $this->errorResponse(message: 'Thời gian suất chiếu bị trùng với suất chiếu khác tại cùng rạp');
        }

        return $this->createRecord(
            model: $this->showtimeModel,
            data: $data,
            message: 'Tạo suất chiếu thành công',
            failMessage: 'Tạo suất chiếu thất bại',
        );
    }

    /**
     * Cập nhật suất chiếu theo ID.
     */
    public function update(UpdateShowtimeRequest $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $showtime = $this->showtimeModel->find($id);

            if (! $showtime) {
                return $this->errorResponse(message: 'Không tìm thấy suất chiếu', code: 404);
            }

            if (! $this->canAccessCinema($request, $showtime->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền cập nhật suất chiếu này', code: 403);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

            if (isset($data['cinema_id']) && ! $this->canAccessCinema($request, $data['cinema_id'])) {
                return $this->errorResponse(message: 'Không có quyền chuyển suất chiếu sang rạp này', code: 403);
            }

            $checkCinemaId = $data['cinema_id'] ?? $showtime->cinema_id;
            $checkStartsAt = $data['starts_at'] ?? $showtime->starts_at;
            $checkEndsAt = $data['ends_at'] ?? $showtime->ends_at;

            if ($this->hasOverlap($checkCinemaId, $checkStartsAt, $checkEndsAt, $id)) {
                return $this->errorResponse(message: 'Thời gian suất chiếu bị trùng với suất chiếu khác tại cùng rạp');
            }

            $showtime->update($data);

            return $this->successResponse(data: $showtime->fresh(), message: 'Cập nhật suất chiếu thành công');
        });
    }

    /**
     * Xoá suất chiếu theo ID.
     */
    public function destroy(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $showtime = $this->showtimeModel->find($id);

            if (! $showtime) {
                return $this->errorResponse(message: 'Không tìm thấy suất chiếu', code: 404);
            }

            if (! $this->canAccessCinema($request, $showtime->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xoá suất chiếu này', code: 403);
            }

            $ticketCount = $showtime->tickets()->count();
            if ($ticketCount > 0) {
                return $this->errorResponse(
                    message: "Không thể xoá suất chiếu đang có {$ticketCount} vé đã đặt",
                );
            }

            $showtime->seats()->delete();
            $showtime->delete();

            return $this->successResponse(data: null, message: 'Xoá suất chiếu thành công');
        });
    }

    /**
     * Kiểm tra trùng lịch suất chiếu cùng rạp.
     */
    private function hasOverlap(string $cinemaId, string $startsAt, string $endsAt, ?string $excludeId = null): bool
    {
        $query = $this->showtimeModel
            ->where('cinema_id', $cinemaId)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt);

        if ($excludeId) {
            $query->where('showtime_id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
