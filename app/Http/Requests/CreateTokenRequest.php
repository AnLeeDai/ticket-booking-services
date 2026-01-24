<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTokenRequest extends FormRequest
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

    // User $user, string $deviceName = 'api', array $abilities = ['*']
    public function rules(): array
    {
        return [
            'device_name' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages()
    {
        return [
            'device_name.string' => 'Tên thiết bị phải là chuỗi ký tự',
            'device_name.max' => 'Tên thiết bị không được vượt quá 100 ký tự',
        ];
    }
}
