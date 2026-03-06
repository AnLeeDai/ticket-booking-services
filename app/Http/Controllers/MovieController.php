<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Services\MovieServices;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function __construct(
        protected MovieServices $movieServices
    ) {}

    public function index(Request $request)
    {
        return $this->movieServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->movieServices->getById($id);
    }

    public function store(CreateMovieRequest $request)
    {
        return $this->movieServices->store($request);
    }

    public function update(UpdateMovieRequest $request, string $id)
    {
        return $this->movieServices->update($request, $id);
    }

    // public function destroy(string $id)
    // {
    //     return $this->movieServices->destroy($id);
    // }
}
