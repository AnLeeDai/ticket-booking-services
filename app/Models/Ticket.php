<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasUuids;

    protected $primaryKey = 'ticket_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'showtime_id',
        'seat_id',
        'user_id',
        'movie_id',
        'code',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:0',
    ];

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class, 'showtime_id', 'showtime_id');
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'seat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'ticket_combos', 'ticket_id', 'combo_id')
            ->withPivot('qty');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'ticket_id', 'ticket_id');
    }
}
