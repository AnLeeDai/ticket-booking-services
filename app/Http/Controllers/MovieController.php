<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieBulkDeleteRequest;
use App\Http\Requests\MovieIndexRequest;
use App\Http\Requests\MovieStoreRequest;
use App\Http\Requests\MovieUpdateRequest;
use App\Models\Movie;
use App\Services\MovieServices;

class MovieController extends Controller
{
    public function __construct(
        protected MovieServices $movieServices
    ) {}

    public function index(MovieIndexRequest $request)
    {
        return $this->movieServices->index($request);
    }

    public function show(Movie $movie)
    {
        return $this->movieServices->show($movie);
    }

    public function store(MovieStoreRequest $request)
    {
        return $this->movieServices->store($request);
    }

    public function update(MovieUpdateRequest $request, Movie $movie)
    {
        return $this->movieServices->update($request, $movie);
    }

    public function destroy(Movie $movie)
    {
        return $this->movieServices->destroy($movie);
    }

    public function bulkDestroy(MovieBulkDeleteRequest $request)
    {
        return $this->movieServices->bulkDestroy($request);
    }
}
