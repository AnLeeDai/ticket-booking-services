<?php

namespace App\Services;

use App\Http\Requests\CreateComboRequest;
use App\Http\Requests\UpdateComboRequest;
use App\Models\Combo;
use Illuminate\Http\Request;

class ComboServices extends Services
{
    public function __construct(
        protected Combo $comboModel
    ) {}

    /**
     * Lấy danh sách combo (có filter, sort, paginate).
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->comboModel,
            request: $request,
            searchableFields: ['name'],
            sortableFields: ['name', 'price', 'stock', 'created_at'],
            message: 'Lấy danh sách combo thành công',
        );
    }

    /**
     * Lấy chi tiết combo theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->comboModel,
            id: $id,
            message: 'Lấy thông tin combo thành công',
            notFoundMessage: 'Không tìm thấy combo',
        );
    }

    /**
     * Tạo combo mới.
     */
    public function store(CreateComboRequest $request)
    {
        return $this->createRecord(
            model: $this->comboModel,
            data: $request->validated(),
            message: 'Tạo combo thành công',
            failMessage: 'Tạo combo thất bại',
        );
    }

    /**
     * Cập nhật combo theo ID.
     */
    public function update(UpdateComboRequest $request, string $id)
    {
        return $this->updateRecord(
            model: $this->comboModel,
            id: $id,
            data: array_filter($request->validated(), fn ($v) => ! is_null($v)),
            message: 'Cập nhật combo thành công',
            notFoundMessage: 'Không tìm thấy combo',
        );
    }

    /**
     * Xoá combo theo ID.
     */
    public function destroy(string $id)
    {
        return $this->tryCatch(function () use ($id) {
            $combo = $this->comboModel->find($id);

            if (! $combo) {
                return $this->errorResponse(message: 'Không tìm thấy combo', code: 404);
            }

            $activeTicketCount = $combo->tickets()
                ->whereIn('status', ['IS_PENDING', 'IN_ACTIVE'])
                ->count();

            if ($activeTicketCount > 0) {
                return $this->errorResponse(
                    message: "Không thể xoá combo đang được sử dụng trong {$activeTicketCount} vé",
                );
            }

            $combo->delete();

            return $this->successResponse(data: null, message: 'Xoá combo thành công');
        });
    }
}
