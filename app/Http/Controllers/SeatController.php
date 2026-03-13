<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSeatRequest;
use App\Http\Requests\UpdateSeatRequest;
use App\Services\SeatServices;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function __construct(
        protected SeatServices $seatServices
    ) {}

    public function index(Request $request)
    {
        return $this->seatServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->seatServices->getById($id);
    }

    public function getByShowtime(string $showtimeId, Request $request)
    {
        return $this->seatServices->getByShowtime($showtimeId, $request);
    }

    public function store(CreateSeatRequest $request)
    {
        return $this->seatServices->store($request);
    }

    public function storeBulk(Request $request)
    {
        return $this->seatServices->storeBulk($request);
    }

    public function update(UpdateSeatRequest $request, string $id)
    {
        return $this->seatServices->update($request, $id);
    }

    public function destroy(Request $request, string $id)
    {
        return $this->seatServices->destroy($request, $id);
    }
}
