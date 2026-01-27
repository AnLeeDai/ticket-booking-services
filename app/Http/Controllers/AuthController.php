<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthServices;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServices $authServices
    ) {}

    public function login(LoginRequest $request)
    {
        return $this->authServices->checkUser($request);
    }

    public function logout(Request $request)
    {
        return $this->authServices->logout($request);
    }

    public function logoutAll(Request $request)
    {
        return $this->authServices->logoutAll($request);
    }

    public function logoutDevice(Request $request, string $deviceName)
    {
        return $this->authServices->logoutDevice($request, $deviceName);
    }

    public function getAllUserLoginDevices(Request $request)
    {
        return $this->authServices->allUserLoginDevices($request);
    }

    public function forgotPassword(
        ForgotPasswordRequest $request
    ) {
        return $this->authServices->sendEmail($request);
    }

    public function resetPassword(
        ResetPasswordRequest $request
    ) {
        return $this->authServices->reset($request);
    }

    public function changePassword(
        ChangePasswordRequest $request
    ) {
        return $this->authServices->changePassword($request);
    }
}
