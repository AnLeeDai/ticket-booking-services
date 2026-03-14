<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEmployeeRoleRequest;
use App\Http\Requests\UpdateEmployeeRoleRequest;
use App\Services\EmployeeRoleServices;
use Illuminate\Http\Request;

class EmployeeRoleController extends Controller
{
    public function __construct(
        protected EmployeeRoleServices $employeeRoleServices
    ) {}

    public function index(Request $request)
    {
        return $this->employeeRoleServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->employeeRoleServices->getById($id);
    }

    public function store(CreateEmployeeRoleRequest $request)
    {
        return $this->employeeRoleServices->store($request);
    }

    public function update(UpdateEmployeeRoleRequest $request, string $id)
    {
        return $this->employeeRoleServices->update($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->employeeRoleServices->destroy($id);
    }
}
