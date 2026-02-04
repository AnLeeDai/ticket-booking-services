<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const STATUS_PENDING = 'IS_PENDING';
    public const STATUS_ACTIVE = 'IN_ACTIVE';
    public const STATUS_INACTIVE = 'UN_ACTIVE';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'title',
        'slug',
        'description',
        'thumb_url',
        'trailer_url',
        'duration_minutes',
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

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->newQuery()
            ->whereNull('deleted_at')
            ->where(function ($query) use ($value, $field) {
                $query->where($field ?? 'id', $value)
                    ->orWhere('slug', $value);
            })
            ->firstOrFail();
    }
}
