<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
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
    ];

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'cinema_id', 'cinema_id');
    }
}
