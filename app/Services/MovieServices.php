<?php

namespace App\Services;

use App\Http\Requests\MovieBulkDeleteRequest;
use App\Http\Requests\MovieIndexRequest;
use App\Http\Requests\MovieStoreRequest;
use App\Http\Requests\MovieUpdateRequest;
use App\Models\Movie;
use Illuminate\Support\Str;

class MovieServices extends Services
{
    public function index(MovieIndexRequest $request)
    {
        try {
            $data = $request->validated();
            $query = Movie::query();

            if (! empty($data['status'])) {
                $query->where('status', $data['status']);
            }

            if (! empty($data['release_date_from'])) {
                $query->whereDate('release_date', '>=', $data['release_date_from']);
            }

            if (! empty($data['release_date_to'])) {
                $query->whereDate('release_date', '<=', $data['release_date_to']);
            }

            if (! empty($data['rating_from'])) {
                $query->where('rating', '>=', $data['rating_from']);
            }

            if (! empty($data['rating_to'])) {
                $query->where('rating', '<=', $data['rating_to']);
            }

            if (! empty($data['language'])) {
                $query->where('language', $data['language']);
            }

            if (! empty($data['age_from'])) {
                $query->where('age', '>=', $data['age_from']);
            }

            if (! empty($data['age_to'])) {
                $query->where('age', '<=', $data['age_to']);
            }

            if (! empty($data['duration_from'])) {
                $query->where('duration', '>=', $data['duration_from']);
            }

            if (! empty($data['duration_to'])) {
                $query->where('duration', '<=', $data['duration_to']);
            }

            $genderIds = $request->input('gender_id');
            if (is_string($genderIds)) {
                $genderIds = array_filter(array_map('trim', explode(',', $genderIds)));
            }

            if (is_array($genderIds)) {
                $genderIds = array_values(array_filter($genderIds, fn ($id) => Str::isUuid($id)));
                if (! empty($genderIds)) {
                    $query->whereIn('gender_id', $genderIds);
                }
            }

            if (! empty($data['q'])) {
                $keyword = trim($data['q']);
                $query->where(function ($sub) use ($keyword) {
                    $like = '%'.$keyword.'%';
                    $sub->where('code', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            }

            $perPage = (int) ($data['per_page'] ?? 10);
            $perPage = max(1, min(100, $perPage));

            $movies = $query
                ->orderByDesc('release_date')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Danh sach phim',
                'data' => $movies->items(),
                'meta' => [
                    'current_page' => $movies->currentPage(),
                    'per_page' => $movies->perPage(),
                    'total' => $movies->total(),
                    'last_page' => $movies->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function show(Movie $movie)
    {
        try {
            return $this->successResponse($movie, 'Chi tiet phim');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function store(MovieStoreRequest $request)
    {
        try {
            $data = $request->validated();

            $movie = Movie::query()->create($data);

            return $this->successResponse($movie, 'Tao phim thanh cong', 201);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function update(MovieUpdateRequest $request, Movie $movie)
    {
        try {
            $data = $request->validated();

            $movie->fill($data);
            $movie->save();

            return $this->successResponse($movie, 'Cap nhat phim thanh cong');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function destroy(Movie $movie)
    {
        try {
            $movie->delete();

            return $this->successResponse(null, 'Xoa phim thanh cong');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }

    public function bulkDestroy(MovieBulkDeleteRequest $request)
    {
        try {
            $ids = $request->validated()['ids'];

            $deleted = Movie::query()->whereIn('movie_id', $ids)->delete();

            return $this->successResponse([
                'deleted' => $deleted,
            ], 'Xoa phim thanh cong');
        } catch (\Throwable $e) {
            return $this->serverErrorResponse(description: $e->getMessage());
        }
    }
}
