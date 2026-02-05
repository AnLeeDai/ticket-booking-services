<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const STATUS_ACTIVE = 'IN_ACTIVE';
    public const STATUS_INACTIVE = 'UN_ACTIVE';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class);
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
