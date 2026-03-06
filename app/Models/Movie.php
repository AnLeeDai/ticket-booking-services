<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasUuids;

    protected $primaryKey = 'movie_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'title',
        'name',
        'slug',
        'description',
        'thumb_url',
        'trailer_url',
        'gallery',
        'duration',
        'language',
        'age',
        'rating',
        'release_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'release_date' => 'date',
        'end_date'     => 'date',
        'rating'       => 'decimal:1',
        'duration'     => 'integer',
        'age'          => 'integer',
        'gallery'      => 'array',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_movie', 'movie_id', 'category_id');
    }
}
