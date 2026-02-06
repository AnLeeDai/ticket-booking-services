<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender_id' => 'sometimes|uuid',
            'code' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'thumb_url' => 'sometimes|string|max:255',
            'trail_url' => 'sometimes|string|max:255',
            'duration' => 'sometimes|date_format:H:i:s',
            'language' => 'sometimes|string|max:100',
            'age' => 'sometimes|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:9.9',
            'release_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:release_date',
            'status' => 'sometimes|in:IN_ACTIVE,UN_ACTIVE,IS_PENDING',
        ];
    }
}
