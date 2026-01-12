<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'role_id' => 'required|integer|exists:roles,id',
            'date_of_birth' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
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
        ];
    }
}
