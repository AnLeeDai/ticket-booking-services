<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Mat khau hien tai la bat buoc',
            'current_password.string' => 'Mat khau hien tai khong dung dinh dang',

            'password.required' => 'Mat khau moi la bat buoc',
            'password.string' => 'Mat khau moi khong dung dinh dang',
            'password.min' => 'Mat khau moi phai co it nhat :min ky tu',
            'password.confirmed' => 'Xac nhan mat khau moi khong khop',
        ];
    }
}
