<?php

namespace App\Services;

use App\Http\Requests\CreateCinemaSaleRequest;
use App\Http\Requests\UpdateCinemaSaleRequest;
use App\Models\CinemaSale;
use Illuminate\Http\Request;

class CinemaSaleServices extends Services
{
    public function __construct(
        protected CinemaSale $cinemaSaleModel
    ) {}

    /**
     * Lấy danh sách doanh thu rạp.
     */
    public function getAll(Request $request)
    {
        $query = $this->cinemaSaleModel->with([
            'cinema:cinema_id,code,name,location',
        ]);

        $cinemaIds = $this->getManagedCinemaIds($request);
        if ($cinemaIds !== null) {
            $query->whereIn('cinema_id', $cinemaIds);
        }

        return $this->filterAndPaginate(
            query: $query,
            request: $request,
            sortableFields: ['sale_date', 'gross_amount', 'created_at'],
            message: 'Lấy danh sách doanh thu rạp thành công',
        );
    }

    /**
     * Lấy chi tiết doanh thu rạp theo ID.
     */
    public function getById(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $sale = $this->cinemaSaleModel->with([
                'cinema:cinema_id,code,name,location',
            ])->find($id);

            if (! $sale) {
                return $this->errorResponse(message: 'Không tìm thấy doanh thu rạp', code: 404);
            }

            if (! $this->canAccessCinema($request, $sale->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xem doanh thu rạp này', code: 403);
            }

            return $this->successResponse(data: $sale, message: 'Lấy thông tin doanh thu rạp thành công');
        });
    }

    /**
     * Tạo doanh thu rạp mới.
     */
    public function store(CreateCinemaSaleRequest $request)
    {
        $data = $request->validated();

        if (! $this->canAccessCinema($request, $data['cinema_id'])) {
            return $this->errorResponse(message: 'Không có quyền tạo doanh thu cho rạp này', code: 403);
        }

        return $this->createRecord(
            model: $this->cinemaSaleModel,
            data: $data,
            message: 'Tạo doanh thu rạp thành công',
            failMessage: 'Tạo doanh thu rạp thất bại',
        );
    }

    /**
     * Cập nhật doanh thu rạp theo ID.
     */
    public function update(UpdateCinemaSaleRequest $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $sale = $this->cinemaSaleModel->find($id);

            if (! $sale) {
                return $this->errorResponse(message: 'Không tìm thấy doanh thu rạp', code: 404);
            }

            if (! $this->canAccessCinema($request, $sale->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền cập nhật doanh thu rạp này', code: 403);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

            if (isset($data['cinema_id']) && ! $this->canAccessCinema($request, $data['cinema_id'])) {
                return $this->errorResponse(message: 'Không có quyền chuyển doanh thu sang rạp này', code: 403);
            }

            $sale->update($data);

            return $this->successResponse(data: $sale->fresh(), message: 'Cập nhật doanh thu rạp thành công');
        });
    }

    /**
     * Xoá doanh thu rạp theo ID.
     */
    public function destroy(Request $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $sale = $this->cinemaSaleModel->find($id);

            if (! $sale) {
                return $this->errorResponse(message: 'Không tìm thấy doanh thu rạp', code: 404);
            }

            if (! $this->canAccessCinema($request, $sale->cinema_id)) {
                return $this->errorResponse(message: 'Không có quyền xoá doanh thu rạp này', code: 403);
            }

            $sale->delete();

            return $this->successResponse(data: null, message: 'Xoá doanh thu rạp thành công');
        });
    }
}
