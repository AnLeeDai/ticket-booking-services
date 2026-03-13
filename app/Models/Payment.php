<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids, SoftDeletes;

    protected $primaryKey = 'payment_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'ticket_id',
        'method',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }
}
