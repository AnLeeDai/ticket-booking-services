<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

abstract class AuthControllerAbstract
{
    protected $authService;
}

interface AuthControllerInterface
{
    public function __construct();

    public function login(LoginRequest $request);
    public function register(RegisterRequest $request);
}

class AuthController extends AuthControllerAbstract implements AuthControllerInterface
{
    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        return $this->authService->login($credentials['email'], $credentials['password']);
    }

    public function register(RegisterRequest $request)
    {
        $credentials = $request->validated();

        return $this->authService->register(
            $credentials['name'],
            $request->input('profile_picture'),
            $credentials['email'],
            $credentials['phone_number'],
            $credentials['address'],
            $request->input('date_of_birth'),
            $credentials['role_id'],
            $credentials['password']
        );
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        return $this->authService->logout($user);
    }
}
