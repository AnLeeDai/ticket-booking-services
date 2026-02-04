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
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            'device_name' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Ho ten la bat buoc.',
            'full_name.string' => 'Ho ten khong dung dinh dang.',
            'full_name.max' => 'Ho ten khong duoc vuot qua :max ky tu.',

            'username.unique' => 'Ten dang nhap da ton tai.',

            'email.required' => 'Email la bat buoc.',
            'email.email' => 'Email khong dung dinh dang.',
            'email.max' => 'Email khong duoc vuot qua :max ky tu.',
            'email.unique' => 'Email da ton tai.',

            'phone.string' => 'So dien thoai khong dung dinh dang.',
            'phone.max' => 'So dien thoai khong duoc vuot qua :max ky tu.',
            'phone.unique' => 'So dien thoai da ton tai.',

            'address.string' => 'Dia chi khong dung dinh dang.',
            'address.max' => 'Dia chi khong duoc vuot qua :max ky tu.',

            'password.required' => 'Mat khau la bat buoc.',
            'password.string' => 'Mat khau khong dung dinh dang.',
            'password.min' => 'Mat khau phai co it nhat :min ky tu.',
            'password.confirmed' => 'Xac nhan mat khau khong khop.',
            'password.regex' => 'Mat khau phai chua it nhat 1 chu hoa, 1 chu thuong, 1 so va 1 ky tu dac biet.',

            'device_name.string' => 'Ten thiet bi khong dung dinh dang.',
            'device_name.max' => 'Ten thiet bi khong duoc vuot qua :max ky tu.',
        ];
    }
}
