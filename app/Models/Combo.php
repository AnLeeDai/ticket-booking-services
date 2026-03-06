<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use HasUuids;

    protected $primaryKey = 'combo_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'stock' => 'integer',
    ];
}
