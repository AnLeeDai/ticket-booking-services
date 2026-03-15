<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->user_id;

        return [
            'full_name' => 'sometimes|string|max:255',
            'user_name' => "sometimes|string|max:50|unique:users,user_name,{$userId},user_id",
            'phone' => "sometimes|nullable|string|max:20|unique:users,phone,{$userId},user_id",
            'dob' => 'sometimes|nullable|date',
            'address' => 'sometimes|nullable|string|max:255',
            'avatar_url' => 'sometimes|nullable|url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.string' => 'Họ tên không đúng định dạng.',
            'full_name.max' => 'Họ tên không được vượt quá :max ký tự.',

            'user_name.string' => 'Tên đăng nhập phải là chuỗi ký tự.',
            'user_name.max' => 'Tên đăng nhập không được vượt quá :max ký tự.',
            'user_name.unique' => 'Tên đăng nhập đã tồn tại.',

            'phone.string' => 'Số điện thoại không đúng định dạng.',
            'phone.max' => 'Số điện thoại không được vượt quá :max ký tự.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',

            'dob.date' => 'Ngày sinh không đúng định dạng.',

            'address.string' => 'Địa chỉ không đúng định dạng.',
            'address.max' => 'Địa chỉ không được vượt quá :max ký tự.',

            'avatar_url.url' => 'URL ảnh đại diện không hợp lệ.',
            'avatar_url.max' => 'URL ảnh đại diện không được vượt quá :max ký tự.',
        ];
    }
}
