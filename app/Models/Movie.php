<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    use HasUuids, SoftDeletes;

    protected $primaryKey = 'movie_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'genre_id',
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
        'end_date' => 'date',
        'rating' => 'decimal:1',
        'duration' => 'integer',
        'age' => 'integer',
        'gallery' => 'array',
    ];

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'genre_id', 'id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_movie', 'movie_id', 'category_id');
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'movie_id', 'movie_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'movie_id', 'movie_id');
    }
}
