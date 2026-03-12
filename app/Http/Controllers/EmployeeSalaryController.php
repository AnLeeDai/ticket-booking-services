<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEmployeeSalaryRequest;
use App\Http\Requests\UpdateEmployeeSalaryRequest;
use App\Services\EmployeeSalaryServices;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    public function __construct(
        protected EmployeeSalaryServices $employeeSalaryServices
    ) {}

    public function index(Request $request)
    {
        return $this->employeeSalaryServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->employeeSalaryServices->getById($id);
    }

    public function store(CreateEmployeeSalaryRequest $request)
    {
        return $this->employeeSalaryServices->store($request);
    }

    public function update(UpdateEmployeeSalaryRequest $request, string $id)
    {
        return $this->employeeSalaryServices->update($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->employeeSalaryServices->destroy($id);
    }
}
