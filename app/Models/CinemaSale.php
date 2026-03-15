<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CinemaSale extends Model
{
    use HasUuids;

    protected $table = 'cinemas_sales';

    protected $primaryKey = 'cinema_sale_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'cinema_id',
        'sale_date',
        'gross_amount',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'gross_amount' => 'decimal:0',
    ];

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id', 'cinema_id');
    }
}
