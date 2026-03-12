<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryServices;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryServices $categoryServices
    ) {}

    public function index(Request $request)
    {
        return $this->categoryServices->getAll($request);
    }

    public function show(string $id)
    {
        return $this->categoryServices->getById($id);
    }

    public function store(CreateCategoryRequest $request)
    {
        return $this->categoryServices->store($request);
    }

    public function update(UpdateCategoryRequest $request, string $id)
    {
        return $this->categoryServices->update($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->categoryServices->destroy($id);
    }
}
