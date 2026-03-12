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
        return $this->filterAndPaginate(
            query: $this->cinemaSaleModel->with([
                'cinema:cinema_id,code,name,location',
            ]),
            request: $request,
            sortableFields: ['sale_date', 'gross_amount', 'created_at'],
            message: 'Lấy danh sách doanh thu rạp thành công',
        );
    }

    /**
     * Lấy chi tiết doanh thu rạp theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->cinemaSaleModel,
            id: $id,
            relations: ['cinema:cinema_id,code,name,location'],
            message: 'Lấy thông tin doanh thu rạp thành công',
            notFoundMessage: 'Không tìm thấy doanh thu rạp',
        );
    }

    /**
     * Tạo doanh thu rạp mới.
     */
    public function store(CreateCinemaSaleRequest $request)
    {
        return $this->createRecord(
            model: $this->cinemaSaleModel,
            data: $request->validated(),
            message: 'Tạo doanh thu rạp thành công',
            failMessage: 'Tạo doanh thu rạp thất bại',
        );
    }

    /**
     * Cập nhật doanh thu rạp theo ID.
     */
    public function update(UpdateCinemaSaleRequest $request, string $id)
    {
        return $this->updateRecord(
            model: $this->cinemaSaleModel,
            id: $id,
            data: array_filter($request->validated(), fn ($v) => ! is_null($v)),
            message: 'Cập nhật doanh thu rạp thành công',
            notFoundMessage: 'Không tìm thấy doanh thu rạp',
        );
    }

    /**
     * Xoá doanh thu rạp theo ID.
     */
    public function destroy(string $id)
    {
        return $this->deleteRecord(
            model: $this->cinemaSaleModel,
            id: $id,
            message: 'Xoá doanh thu rạp thành công',
            notFoundMessage: 'Không tìm thấy doanh thu rạp',
        );
    }
}
