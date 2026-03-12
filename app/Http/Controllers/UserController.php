<?php

namespace App\Http\Controllers;

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
}
