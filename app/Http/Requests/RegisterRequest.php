<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => 'nullable|string|max:50|unique:users,user_name',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            'device_name' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'user_name.string' => 'Tên đăng nhập phải là chuỗi ký tự.',
            'user_name.max' => 'Tên đăng nhập không được vượt quá :max ký tự.',
            'user_name.unique' => 'Tên đăng nhập đã tồn tại.',

            'full_name.required' => 'Họ tên là bắt buộc.',
            'full_name.string' => 'Họ tên không đúng định dạng.',
            'full_name.max' => 'Họ tên không được vượt quá :max ký tự.',

            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá :max ký tự.',
            'email.unique' => 'Email đã tồn tại.',

            'phone.string' => 'Số điện thoại không đúng định dạng.',
            'phone.max' => 'Số điện thoại không được vượt quá :max ký tự.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',

            'dob.date' => 'Ngày sinh không đúng định dạng.',

            'address.string' => 'Địa chỉ không đúng định dạng.',
            'address.max' => 'Địa chỉ không được vượt quá :max ký tự.',

            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.string' => 'Mật khẩu không đúng định dạng.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.',

            'device_name.string' => 'Tên thiết bị không đúng định dạng.',
            'device_name.max' => 'Tên thiết bị không được vượt quá :max ký tự.',
        ];
    }
}
