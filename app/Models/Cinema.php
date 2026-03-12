<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cinema extends Model
{
    use HasUuids;

    protected $primaryKey = 'cinema_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'location',
        'active',
        'manager_id',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id', 'user_id');
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'cinema_id', 'cinema_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'cinema_id', 'cinema_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(CinemaSale::class, 'cinema_id', 'cinema_id');
    }
}
