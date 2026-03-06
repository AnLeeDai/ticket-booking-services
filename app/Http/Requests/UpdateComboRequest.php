<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComboRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $comboId = $this->route('id');

        return [
            'name'  => ['sometimes', 'required', 'string', 'max:255', Rule::unique('combos', 'name')->ignore($comboId, 'combo_id')],
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên combo không được để trống',
            'name.max'      => 'Tên combo không được vượt quá 255 ký tự',
            'name.unique'   => 'Tên combo đã tồn tại',
            'price.numeric' => 'Giá combo phải là số',
            'price.min'     => 'Giá combo không được âm',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên',
            'stock.min'     => 'Số lượng tồn kho không được âm',
        ];
    }
}
