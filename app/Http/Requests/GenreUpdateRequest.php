<?php

namespace App\Http\Requests;

use App\Models\Genre;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $genre = $this->route('genre');
        $genreId = $genre?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('genres', 'name')->ignore($genreId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('genres', 'slug')->ignore($genreId)],
            'active' => ['nullable', Rule::in(Genre::STATUSES)],
        ];
    }
}
