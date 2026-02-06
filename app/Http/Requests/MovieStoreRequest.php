<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender_id' => 'required|uuid',
            'code' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumb_url' => 'required|string|max:255',
            'trail_url' => 'required|string|max:255',
            'duration' => 'required|date_format:H:i:s',
            'language' => 'required|string|max:100',
            'age' => 'required|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:9.9',
            'release_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:release_date',
            'status' => 'required|in:IN_ACTIVE,UN_ACTIVE,IS_PENDING',
        ];
    }
}
