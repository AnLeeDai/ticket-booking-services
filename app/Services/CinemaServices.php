<?php

namespace App\Services;

use App\Http\Requests\CreateCinemaRequest;
use App\Http\Requests\UpdateCinemaRequest;
use App\Models\Cinema;
use Illuminate\Http\Request;

class CinemaServices extends Services
{
    public function __construct(
        protected Cinema $cinemaModel
    ) {}

    /**
     * Lấy danh sách rạp chiếu (có filter, sort, paginate).
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->cinemaModel,
            request: $request,
            searchableFields: ['name', 'location', 'code'],
            sortableFields: ['name', 'code', 'created_at'],
            message: 'Lấy danh sách rạp chiếu thành công',
        );
    }

    /**
     * Lấy chi tiết rạp chiếu theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->cinemaModel,
            id: $id,
            message: 'Lấy thông tin rạp chiếu thành công',
            notFoundMessage: 'Không tìm thấy rạp chiếu',
        );
    }

    /**
     * Tạo rạp chiếu mới (code tự động sinh).
     */
    public function store(CreateCinemaRequest $request)
    {
        return $this->createRecord(
            model: $this->cinemaModel,
            data: array_merge($request->validated(), [
                'code' => $this->generateCode(),
                'active' => $request->active ?? 'IN_ACTIVE',
            ]),
            message: 'Tạo rạp chiếu thành công',
            failMessage: 'Tạo rạp chiếu thất bại',
        );
    }

    /**
     * Cập nhật rạp chiếu theo ID.
     */
    public function update(UpdateCinemaRequest $request, string $id)
    {
        if (! $this->canAccessCinema($request, $id)) {
            return $this->errorResponse(message: 'Không có quyền cập nhật rạp này', code: 403);
        }

        $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

        // Manager không được thay đổi manager_id và trạng thái
        if ($this->isManager($request)) {
            unset($data['manager_id'], $data['active']);
        }

        return $this->updateRecord(
            model: $this->cinemaModel,
            id: $id,
            data: $data,
            message: 'Cập nhật rạp chiếu thành công',
            notFoundMessage: 'Không tìm thấy rạp chiếu',
        );
    }

    /**
     * Xoá rạp chiếu theo ID.
     */
    public function destroy(string $id)
    {
        return $this->tryCatch(function () use ($id) {
            $cinema = $this->cinemaModel->find($id);

            if (! $cinema) {
                return $this->errorResponse(message: 'Không tìm thấy rạp chiếu', code: 404);
            }

            $activeShowtimes = $cinema->showtimes()->where('ends_at', '>', now())->count();
            if ($activeShowtimes > 0) {
                return $this->errorResponse(
                    message: "Không thể xoá rạp đang có {$activeShowtimes} suất chiếu chưa kết thúc",
                );
            }

            $employeeCount = $cinema->employees()->count();
            if ($employeeCount > 0) {
                return $this->errorResponse(
                    message: "Không thể xoá rạp đang có {$employeeCount} nhân viên",
                );
            }

            $cinema->delete();

            return $this->successResponse(data: null, message: 'Xoá rạp chiếu thành công');
        });
    }

    /**
     * Tự sinh mã rạp theo format: CN-XXXXXX
     * Ví dụ: CN-000001
     */
    private function generateCode(): string
    {
        $count = $this->cinemaModel->count() + 1;
        $code = sprintf('CN-%06d', $count);

        while ($this->cinemaModel->where('code', $code)->exists()) {
            $count++;
            $code = sprintf('CN-%06d', $count);
        }

        return $code;
    }
}
