<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|in:IN_ACTIVE,UN_ACTIVE,IS_PENDING',
            'release_date_from' => 'nullable|date',
            'release_date_to' => 'nullable|date|after_or_equal:release_date_from',
            'rating_from' => 'nullable|numeric|min:0|max:9.9',
            'rating_to' => 'nullable|numeric|min:0|max:9.9',
            'language' => 'nullable|string|max:100',
            'age_from' => 'nullable|integer|min:0',
            'age_to' => 'nullable|integer|min:0',
            'duration_from' => 'nullable|date_format:H:i:s',
            'duration_to' => 'nullable|date_format:H:i:s',
            'gender_id' => 'nullable',
            'q' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
