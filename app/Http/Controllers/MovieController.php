<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query()->with('genres');

        $query->when($request->query('status'), function ($q, $status) {
            if (in_array($status, Movie::STATUSES, true)) {
                $q->where('status', $status);
            }
        });

        $query->when($request->query('release_date_from'), fn ($q, $from) => $q->whereDate('release_date', '>=', $from));
        $query->when($request->query('release_date_to'), fn ($q, $to) => $q->whereDate('release_date', '<=', $to));

        $query->when($request->query('rating_from'), fn ($q, $from) => $q->where('rating', '>=', $from));
        $query->when($request->query('rating_to'), fn ($q, $to) => $q->where('rating', '<=', $to));

        $query->when($request->query('language'), fn ($q, $language) => $q->where('language', $language));

        $query->when($request->query('age_from'), fn ($q, $from) => $q->where('age', '>=', $from));
        $query->when($request->query('age_to'), fn ($q, $to) => $q->where('age', '<=', $to));

        $query->when($request->query('duration_from'), fn ($q, $from) => $q->where('duration_minutes', '>=', $from));
        $query->when($request->query('duration_to'), fn ($q, $to) => $q->where('duration_minutes', '<=', $to));

        $query->when($request->query('genres'), function ($q, $genres) {
            $ids = is_string($genres) ? array_filter(explode(',', $genres)) : (array) $genres;
            if (! empty($ids)) {
                $q->whereHas('genres', fn ($genreQuery) => $genreQuery->whereIn('genres.id', $ids));
            }
        });

        $query->when($request->query('q'), function ($q, $keyword) {
            $q->where(function ($subQuery) use ($keyword) {
                $subQuery->where('title', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        });

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $paginator = $query
            ->orderByDesc('release_date')
            ->orderBy('title')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'success' => true,
            'message' => 'Danh sách phim',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Movie $movie)
    {
        $movie->load('genres');

        return $this->successResponse($movie, 'Chi tiết phim');
    }
}
