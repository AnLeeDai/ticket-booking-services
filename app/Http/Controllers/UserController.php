<?php

namespace App\Http\Controllers;

use App\Services\UserServices;

class UserController extends Controller
{
    public function __construct(
        protected UserServices $userServices
    ) {}

    public function index()
    {
        return $this->userServices->findAllUsers();
    }

    public function profile()
    {
        return $this->userServices->me();
    }
}
