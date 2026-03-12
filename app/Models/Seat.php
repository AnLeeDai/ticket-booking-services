<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Seat extends Model
{
    use HasUuids;

    protected $primaryKey = 'seat_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'showtime_id',
        'seat_code',
        'seat_type',
        'price',
        'active',
        'hold_until',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'hold_until' => 'datetime',
    ];

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class, 'showtime_id', 'showtime_id');
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'seat_id', 'seat_id');
    }
}
