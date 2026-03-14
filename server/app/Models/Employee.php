<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUuids, SoftDeletes;

    protected $primaryKey = 'employee_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_role_id',
        'user_id',
        'cinema_id',
        'name',
        'code',
        'hire_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'end_date' => 'date',
    ];

    public function employeeRole(): BelongsTo
    {
        return $this->belongsTo(EmployeeRole::class, 'employee_role_id', 'employee_role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id', 'cinema_id');
    }

    public function salary(): HasOne
    {
        return $this->hasOne(EmployeeSalary::class, 'employee_id', 'employee_id');
    }
}
