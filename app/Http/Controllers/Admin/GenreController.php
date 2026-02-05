<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenreStoreRequest;
use App\Http\Requests\GenreUpdateRequest;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    public function index(Request $request)
    {
        $query = Genre::query();

        $query->when($request->query('q'), function ($q, $keyword) {
            $q->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });
        });

        $query->when($request->query('created_from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from));
        $query->when($request->query('created_to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to));

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'success' => true,
            'message' => 'Danh sach the loai',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(GenreStoreRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? null, $data['name']);

        $genre = Genre::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'active' => $data['active'] ?? Genre::STATUS_ACTIVE,
        ]);

        return $this->successResponse($genre, 'Tao the loai thanh cong', 201);
    }

    public function show(Genre $genre)
    {
        $genre->loadCount('movies');

        return $this->successResponse($genre, 'Chi tiet the loai');
    }

    public function update(GenreUpdateRequest $request, Genre $genre)
    {
        $data = $request->validated();

        if (array_key_exists('slug', $data)) {
            $data['slug'] = $this->makeUniqueSlug(
                $data['slug'],
                $data['name'] ?? $genre->name,
                $genre->id
            );
        }

        $genre->update($data);

        return $this->successResponse($genre, 'Cap nhat the loai thanh cong');
    }

    public function toggle(Genre $genre)
    {
        $genre->active = $genre->active === Genre::STATUS_ACTIVE
            ? Genre::STATUS_INACTIVE
            : Genre::STATUS_ACTIVE;
        $genre->save();

        return $this->successResponse($genre, 'Cap nhat trang thai the loai thanh cong');
    }

    private function makeUniqueSlug(?string $slug, string $name, ?string $ignoreId = null): string
    {
        $base = $slug ?: Str::slug($name);
        $base = $base !== '' ? $base : Str::slug(Str::uuid());

        $candidate = $base;
        $suffix = 1;

        while ($this->slugExists($candidate, $ignoreId)) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?string $ignoreId = null): bool
    {
        return Genre::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
