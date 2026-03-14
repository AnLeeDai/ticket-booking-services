<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    use HasUuids;

    protected $primaryKey = 'employee_salary_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'bank_number',
        'bank_name',
        'net_salary',
        'bonus',
        'total_earn',
        'payment_status',
    ];

    protected $casts = [
        'net_salary' => 'decimal:0',
        'bonus' => 'decimal:0',
        'total_earn' => 'decimal:0',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
