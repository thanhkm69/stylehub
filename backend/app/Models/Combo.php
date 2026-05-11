<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'combo_type',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'status',
        'display',
    ];

    protected $casts = [
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'status'         => 'boolean',
        'discount_value' => 'decimal:2',
    ];
}
