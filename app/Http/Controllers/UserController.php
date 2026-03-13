<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserServices;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserServices $userServices
    ) {}

    public function index(Request $request)
    {
        return $this->userServices->getAll($request);
    }

    public function profile()
    {
        return $this->userServices->getProfile();
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->userServices->updateProfile($request);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        return $this->userServices->updateUser($request, $id);
    }

    public function destroy(string $id)
    {
        return $this->userServices->deleteUser($id);
    }
}
