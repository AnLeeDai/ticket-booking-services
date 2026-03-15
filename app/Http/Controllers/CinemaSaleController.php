<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCinemaSaleRequest;
use App\Http\Requests\UpdateCinemaSaleRequest;
use App\Services\CinemaSaleServices;
use Illuminate\Http\Request;

class CinemaSaleController extends Controller
{
    public function __construct(
        protected CinemaSaleServices $cinemaSaleServices
    ) {}

    public function index(Request $request)
    {
        return $this->cinemaSaleServices->getAll($request);
    }

    public function show(Request $request, string $id)
    {
        return $this->cinemaSaleServices->getById($request, $id);
    }

    public function store(CreateCinemaSaleRequest $request)
    {
        return $this->cinemaSaleServices->store($request);
    }

    public function update(UpdateCinemaSaleRequest $request, string $id)
    {
        return $this->cinemaSaleServices->update($request, $id);
    }

    public function destroy(Request $request, string $id)
    {
        return $this->cinemaSaleServices->destroy($request, $id);
    }
}
