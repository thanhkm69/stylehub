<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'hobbies',
        'occupation',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
