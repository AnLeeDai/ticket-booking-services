<?php

namespace App\Models;

use App\Notifications\ResetPasswordCodeNotification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $with = ['role'];

    protected $fillable = [
        'role_id',
        'full_name',
        'user_name',
        'email',
        'phone',
        'dob',
        'address',
        'avatar_url',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'role_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id', 'user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id', 'user_id');
    }

    public function managedCinemas(): HasMany
    {
        return $this->hasMany(Cinema::class, 'manager_id', 'user_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordCodeNotification($token));
    }
}
