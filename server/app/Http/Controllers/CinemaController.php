<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCinemaRequest;
use App\Http\Requests\UpdateCinemaRequest;
use App\Services\CinemaServices;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    public function __construct(
        protected CinemaServices $cinemaServices
    ) {}

    public function index(Request $request)
    {
        return $this->cinemaServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->cinemaServices->getById($id);
    }

    public function store(CreateCinemaRequest $request)
    {
        return $this->cinemaServices->store($request);
    }

    public function update(UpdateCinemaRequest $request, string $id)
    {
        return $this->cinemaServices->update($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->cinemaServices->destroy($id);
    }
}
