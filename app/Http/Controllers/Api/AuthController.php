<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Services\UserService;

abstract class AuthControllerAbstract
{
    protected $userService;
}

interface AuthControllerInterface
{
    public function __construct();

    public function login(Request $request);
}

class AuthController extends AuthControllerAbstract implements AuthControllerInterface
{
    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Định dạng email không hợp lệ',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự',
        ]);

        return $this->userService->isUserExist($credentials['email'], $credentials['password']);
    }

    public function register(Request $request)
    {
        $credentials = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'phone_number' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'role_id' => 'required|integer|exists:roles,id',
                'date_of_birth' => 'nullable|date',
            ],
            [
                'name.required' => 'Tên là bắt buộc',
                'name.string' => 'Tên phải là chuỗi ký tự',
                'name.max' => 'Tên không được vượt quá :max ký tự',
                'email.required' => 'Email là bắt buộc',
                'email.email' => 'Định dạng email không hợp lệ',
                'email.unique' => 'Email đã được sử dụng',
                'password.required' => 'Mật khẩu là bắt buộc',
                'password.string' => 'Mật khẩu phải là chuỗi ký tự',
                'password.min' => 'Mật khẩu phải có ít nhất :min ký tự',
                'phone_number.required' => 'Số điện thoại là bắt buộc',
                'phone_number.string' => 'Số điện thoại phải là chuỗi ký tự',
                'phone_number.max' => 'Số điện thoại không được vượt quá :max ký tự',
                'address.required' => 'Địa chỉ là bắt buộc',
                'address.string' => 'Địa chỉ phải là chuỗi ký tự',
                'address.max' => 'Địa chỉ không được vượt quá :max ký tự',
                'date_of_birth.date' => 'Ngày sinh không hợp lệ',
                'role_id.required' => 'Vai trò là bắt buộc',
            ]
        );

        return $this->userService->registerUser(
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
        return $this->userService->logoutUser($user);
    }
}
