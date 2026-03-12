<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\EmployeeServices;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeServices $employeeServices
    ) {}

    public function index(Request $request)
    {
        return $this->employeeServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->employeeServices->getById($id);
    }

    public function store(CreateEmployeeRequest $request)
    {
        return $this->employeeServices->store($request);
    }

    public function update(UpdateEmployeeRequest $request, string $id)
    {
        return $this->employeeServices->update($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->employeeServices->destroy($id);
    }
}
