<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCinemaSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $saleId = $this->route('id');

        return [
            'cinema_id' => 'sometimes|required|uuid|exists:cinemas,cinema_id',
            'sale_date' => [
                'sometimes',
                'required',
                'date',
                \Illuminate\Validation\Rule::unique('cinemas_sales')->where(function ($query) {
                    return $query->where('cinema_id', $this->cinema_id ?? $this->route('cinema_id'));
                })->ignore($saleId, 'cinema_sale_id'),
            ],
            'gross_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'cinema_id.required' => 'Rạp chiếu không được để trống',
            'cinema_id.uuid' => 'ID rạp chiếu không hợp lệ',
            'cinema_id.exists' => 'Rạp chiếu không tồn tại',

            'sale_date.required' => 'Ngày doanh thu không được để trống',
            'sale_date.date' => 'Ngày doanh thu không hợp lệ',

            'gross_amount.numeric' => 'Tổng doanh thu phải là số',
            'gross_amount.min' => 'Tổng doanh thu không được âm',
        ];
    }
}
