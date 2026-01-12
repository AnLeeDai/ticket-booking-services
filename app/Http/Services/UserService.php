<?php

namespace App\Http\Services;

use DateTime;

use App\Http\Traits\ResponseTrait;
use App\Models\User;

abstract class UserServiceAbstract
{
    protected $userModel;

    protected $now;
}

interface UserServiceInterface
{
    public function __construct();

    public function findUserByEmail(string $email);
    public function findUserByPhoneNumber(string $phoneNumber);
}

class UserService extends UserServiceAbstract implements UserServiceInterface
{
    use ResponseTrait;

    public function __construct()
    {
        $this->userModel = new User();
        $this->now = new DateTime();
    }

    public function findUserByEmail(string $email)
    {
        try {
            $isExistEmail = $this->userModel->where('email', $email)->first();

            if (!$isExistEmail) {
                return $this->responseNotFound('Email người dùng không tồn tại');
            }

            return $isExistEmail;
        } catch (\Exception $e) {
            return $this->responseServerError($e->getMessage());
        }
    }

    public function findUserByPhoneNumber(string $phoneNumber)
    {
        try {
            $isExistPhoneNumber = $this->userModel->where('phone_number', $phoneNumber)->first();

            if (!$isExistPhoneNumber) {
                return $this->responseNotFound('Số điện thoại người dùng không tồn tại');
            }

            return $isExistPhoneNumber;
        } catch (\Exception $e) {
            return $this->responseServerError($e->getMessage());
        }
    }
}
