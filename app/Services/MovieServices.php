<?php

namespace App\Services;

use App\Http\Requests\CreateMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MovieServices extends Services
{
    public function __construct(
        protected Movie $movieModel
    ) {}

    /**
     * Lấy danh sách phim (có filter, sort, paginate).
     */
    public function getAll(Request $request)
    {
        return $this->filterAndPaginate(
            query: $this->movieModel->with('categories:id,name,slug'),
            request: $request,
            searchableFields: ['title', 'name', 'code', 'language'],
            sortableFields: ['name', 'title', 'release_date', 'rating', 'duration', 'created_at'],
            message: 'Lấy danh sách phim thành công',
        );
    }

    /**
     * Lấy chi tiết phim theo ID.
     */
    public function getById(string $id)
    {
        return $this->findById(
            model: $this->movieModel,
            id: $id,
            relations: ['categories:id,name,slug'],
            message: 'Lấy thông tin phim thành công',
            notFoundMessage: 'Không tìm thấy phim',
        );
    }

    /**
     * Tạo phim mới (code tự động sinh, gắn nhiều thể loại).
     */
    public function store(CreateMovieRequest $request)
    {
        return $this->tryCatch(function () use ($request) {
            $data = $request->validated();
            $slug = Str::slug($data['name']);

            if ($this->movieModel->where('slug', $slug)->exists()) {
                return $this->errorResponse(message: 'Slug đã tồn tại, vui lòng đổi tên phim');
            }

            $categoryIds = $data['category_ids'];
            unset($data['category_ids']);

            $movie = DB::transaction(function () use ($data, $slug, $categoryIds) {
                $movie = $this->movieModel->create(array_merge($data, [
                    'code' => $this->generateCode(),
                    'slug' => $slug,
                ]));

                $movie->categories()->sync($categoryIds);

                return $movie;
            });

            return $this->successResponse(
                data: $movie->load('categories:id,name,slug'),
                message: 'Tạo phim thành công',
            );
        });
    }

    /**
     * Cập nhật thông tin phim theo ID (sync lại thể loại nếu có truyền).
     */
    public function update(UpdateMovieRequest $request, string $id)
    {
        return $this->tryCatch(function () use ($request, $id) {
            $movie = $this->movieModel->find($id);

            if (! $movie) {
                return $this->errorResponse(message: 'Không tìm thấy phim', code: 404);
            }

            $data = array_filter($request->validated(), fn ($v) => ! is_null($v));
            $categoryIds = $data['category_ids'] ?? null;
            unset($data['category_ids']);

            if (isset($data['name'])) {
                $slug = Str::slug($data['name']);

                if ($this->movieModel->where('slug', $slug)->where('movie_id', '!=', $id)->exists()) {
                    return $this->errorResponse(message: 'Slug đã tồn tại, vui lòng đổi tên phim');
                }

                $data['slug'] = $slug;
            }

            DB::transaction(function () use ($movie, $data, $categoryIds) {
                $movie->update($data);

                if ($categoryIds !== null) {
                    $movie->categories()->sync($categoryIds);
                }
            });

            return $this->successResponse(
                data: $movie->fresh()->load('categories:id,name,slug'),
                message: 'Cập nhật phim thành công',
            );
        });
    }

    /**
     * Tự sinh mã phim theo format: MV-YYYYMM-XXXX
     * Ví dụ: MV-202603-0001
     */
    private function generateCode(): string
    {
        $prefix = 'MV';
        $date = now()->format('Ym');
        $like = "{$prefix}-{$date}-%";

        $count = $this->movieModel->where('code', 'like', $like)->count() + 1;
        $code = sprintf('%s-%s-%04d', $prefix, $date, $count);

        // Đảm bảo không trùng trong trường hợp race condition
        while ($this->movieModel->where('code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%s-%04d', $prefix, $date, $count);
        }

        return $code;
    }
}
