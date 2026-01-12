<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Hash;
use DateTime;

use App\Http\Services\UserService;
use App\Models\User;
use App\Models\Role;
use App\Http\Traits\ResponseTrait;

abstract class AuthServiceAbstract
{
    protected $userService;
    protected $roleModel;
    protected $userModel;
    protected $profilePicture = "https://testingbot.com/free-online-tools/random-avatar/45";
    protected $now;
}

interface AuthServiceInterface
{
    public function __construct();
    public function login(string $email, string $password);
    public function register(
        string $name,
        ?string $profilePicture,
        string $email,
        string $phoneNumber,
        string $address,
        ?string $dateOfBirth,
        int $roleId,
        string $password
    );
    public function logout(User $user);
}

class AuthService extends AuthServiceAbstract implements AuthServiceInterface
{
    use ResponseTrait;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->now = new DateTime();
    }

    public function login(string $email, string $password)
    {
        try {
            $user = $this->userService->findUserByEmail($email);

            if (!$user) {
                return $this->responseNotFound('Email người dùng không tồn tại');
            }

            if (!Hash::check($password, $user->password)) {
                return $this->responseUnauthorized('Mật khẩu không đúng');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->responseSuccess([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Đăng nhập thành công');
        } catch (\Exception $e) {
            return $this->responseServerError($e->getMessage());
        }
    }

    public function logout(User $user)
    {
        try {
            $user->tokens()->delete();

            return $this->responseSuccess(null, 'Đăng xuất thành công');
        } catch (\Exception $e) {
            return $this->responseServerError($e->getMessage());
        }
    }

    public function register(
        string $name,
        ?string $profilePicture,
        string $email,
        string $phoneNumber,
        string $address,
        ?string $dateOfBirth,
        int $roleId,
        string $password
    ) {
        try {
            $emailLookup = $this->userService->findUserByEmail($email);
            if ($emailLookup instanceof User) {
                return $this->responseConflict('Email đã được sử dụng');
            }
            if (is_object($emailLookup) && method_exists($emailLookup, 'getStatusCode') && $emailLookup->getStatusCode() !== 404) {
                return $emailLookup;
            }

            $phoneLookup = $this->userService->findUserByPhoneNumber($phoneNumber);
            if ($phoneLookup instanceof User) {
                return $this->responseConflict('Số điện thoại đã được sử dụng');
            }
            if (is_object($phoneLookup) && method_exists($phoneLookup, 'getStatusCode') && $phoneLookup->getStatusCode() !== 404) {
                return $phoneLookup;
            }

            $role = $this->roleModel->find($roleId);
            if ($role && strtolower($role->name) !== 'customer') {
                return $this->responseForbidden('Bạn không có quyền tạo người dùng với vai trò này');
            }

            $data = [
                'username' => explode('@', $email)[0],
                'name' => $name,
                'profile_picture' => $profilePicture ?? "{$this->profilePicture}?" . rand(1, 10),
                'email' => $email,
                'phone_number' => $phoneNumber,
                'address' => $address,
                'date_of_birth' => $dateOfBirth ?? $this->now->format('d-m-Y'),
                'role_id' => $roleId,
                'password' => Hash::make($password),
            ];

            $newUser = $this->userModel->create($data);

            if (!$newUser) {
                return $this->responseServerError('Đăng ký người dùng thất bại');
            }

            return $this->responseSuccess($newUser, 'Đăng ký người dùng thành công', 201);
        } catch (\Exception $e) {
            return $this->responseServerError($e->getMessage());
        }
    }
}
