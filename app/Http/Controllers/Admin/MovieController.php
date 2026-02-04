<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovieBulkDeleteRequest;
use App\Http\Requests\MovieStoreRequest;
use App\Models\Movie;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function store(MovieStoreRequest $request)
    {
        $data = $request->validated();

        $slug = $this->makeUniqueSlug($data['slug'] ?? null, $data['title']);

        $movie = Movie::query()->create([
            'code' => $data['code'],
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'thumb_url' => $data['thumb_url'] ?? null,
            'trailer_url' => $data['trailer_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'language' => $data['language'],
            'age' => $data['age'],
            'rating' => $data['rating'] ?? null,
            'release_date' => $data['release_date'],
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'],
        ]);

        $movie->genres()->sync($data['genres'] ?? []);

        $movie->load('genres');

        return $this->successResponse($movie, 'Tạo phim thành công', 201);
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return $this->successResponse(null, 'Xóa phim thành công');
    }

    public function bulkDestroy(MovieBulkDeleteRequest $request)
    {
        $ids = $request->validated()['ids'];

        $deleted = Movie::query()
            ->whereIn('id', $ids)
            ->delete();

        return $this->successResponse(
            ['deleted' => $deleted],
            'Xóa nhiều phim thành công'
        );
    }

    private function makeUniqueSlug(?string $slug, string $title): string
    {
        $base = $slug ?: Str::slug($title);
        $base = $base !== '' ? $base : Str::slug(Str::uuid());

        $candidate = $base;
        $suffix = 1;

        while (Movie::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
