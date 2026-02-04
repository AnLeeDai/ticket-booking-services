<?php

namespace App\Http\Requests;

use App\Models\Movie;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MovieStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:movies,code'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:movies,slug'],
            'description' => ['nullable', 'string'],
            'thumb_url' => ['nullable', 'string', 'max:500', 'url'],
            'trailer_url' => ['nullable', 'string', 'max:500', 'url'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'language' => ['required', 'string', 'max:100'],
            'age' => ['required', 'integer', 'min:0'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:9.9'],
            'release_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:release_date'],
            'status' => ['required', Rule::in(Movie::STATUSES)],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['string', 'distinct', 'exists:genres,id'],
        ];
    }
}
