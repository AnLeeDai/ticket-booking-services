<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateComboRequest;
use App\Http\Requests\UpdateComboRequest;
use App\Services\ComboServices;
use Illuminate\Http\Request;

class ComboController extends Controller
{
    public function __construct(
        protected ComboServices $comboServices
    ) {}

    public function index(Request $request)
    {
        return $this->comboServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->comboServices->getById($id);
    }

    public function store(CreateComboRequest $request)
    {
        return $this->comboServices->store($request);
    }

    public function update(UpdateComboRequest $request, string $id)
    {
        return $this->comboServices->update($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->comboServices->destroy($id);
    }
}
