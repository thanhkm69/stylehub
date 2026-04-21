<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
    ];
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
