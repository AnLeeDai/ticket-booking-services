<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_name' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'device_name.string' => 'Tên thiết bị phải là chuỗi ký tự',
            'device_name.max' => 'Tên thiết bị không được vượt quá 100 ký tự',
        ];
    }
}
