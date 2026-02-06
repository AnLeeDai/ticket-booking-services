<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasUuids;

    protected $primaryKey = 'movie_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'gender_id',
        'code',
        'title',
        'name',
        'slug',
        'description',
        'thumb_url',
        'trail_url',
        'duration',
        'language',
        'age',
        'rating',
        'release_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'end_date' => 'date',
            'rating' => 'decimal:1',
        ];
    }
}
