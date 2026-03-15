<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateShowtimeRequest;
use App\Http\Requests\UpdateShowtimeRequest;
use App\Services\ShowtimeServices;
use Illuminate\Http\Request;

class ShowtimeController extends Controller
{
    public function __construct(
        protected ShowtimeServices $showtimeServices
    ) {}

    public function index(Request $request)
    {
        return $this->showtimeServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->showtimeServices->getById($id);
    }

    public function store(CreateShowtimeRequest $request)
    {
        return $this->showtimeServices->store($request);
    }

    public function update(UpdateShowtimeRequest $request, string $id)
    {
        return $this->showtimeServices->update($request, $id);
    }

    public function destroy(Request $request, string $id)
    {
        return $this->showtimeServices->destroy($request, $id);
    }
}
