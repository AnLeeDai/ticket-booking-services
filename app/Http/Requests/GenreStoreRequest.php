<?php

namespace App\Http\Requests;

use App\Models\Genre;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:genres,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:genres,slug'],
            'active' => ['nullable', Rule::in(Genre::STATUSES)],
        ];
    }
}
