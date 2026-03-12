<?php

namespace App\Services;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryServices extends Services
{
    public function __construct(
        protected Category $categoryModel
    ) {}

    /**
     * Lấy danh sách danh mục.
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->categoryModel,
            request: $request,
            searchableFields: ['name', 'description'],
            sortableFields: ['name', 'slug', 'created_at'],
            message: 'Lấy danh sách danh mục thành công',
        );
    }

    /**
     * Lấy chi tiết danh mục theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->categoryModel,
            id: $id,
            message: 'Lấy danh mục thành công',
            notFoundMessage: 'Không tìm thấy danh mục',
        );
    }

    /**
     * Tạo danh mục mới.
     */
    public function store(CreateCategoryRequest $request)
    {
        $slug = Str::slug($request->name);

        if ($this->categoryModel->where('slug', $slug)->exists()) {
            return $this->errorResponse(message: 'Slug đã tồn tại');
        }

        return $this->createRecord(
            model: $this->categoryModel,
            data: [
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
            ],
            message: 'Tạo danh mục thành công',
            failMessage: 'Tạo danh mục thất bại',
        );
    }

    /**
     * Cập nhật danh mục theo ID.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        if (! $id) {
            return $this->errorResponse(message: 'ID danh mục không hợp lệ');
        }

        $data = array_filter($request->validated(), fn ($v) => ! is_null($v));

        if (isset($data['name'])) {
            $slug = Str::slug($data['name']);

            $exists = $this->categoryModel
                ->where('slug', $slug)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->errorResponse(message: 'Slug đã tồn tại');
            }

            $data['slug'] = $slug;
        }

        return $this->updateRecord(
            model: $this->categoryModel,
            id: $id,
            data: $data,
            message: 'Cập nhật danh mục thành công',
            notFoundMessage: 'Không tìm thấy danh mục',
        );
    }

    /**
     * Xoá danh mục theo ID (kiểm tra phim đang gắn).
     */
    public function destroy(string $id)
    {
        return $this->tryCatch(function () use ($id) {
            $category = $this->categoryModel->find($id);

            if (! $category) {
                return $this->errorResponse(message: 'Không tìm thấy danh mục', code: 404);
            }

            $movieCount = Movie::whereHas('categories', fn ($q) => $q->where('categories.id', $id))->count();

            if ($movieCount > 0) {
                return $this->errorResponse(
                    message: "Không thể xoá danh mục đang được gắn với {$movieCount} phim",
                );
            }

            $category->delete();

            return $this->successResponse(data: null, message: 'Xoá danh mục thành công');
        });
    }
}
