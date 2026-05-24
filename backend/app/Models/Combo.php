<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'status',
        'display',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'status' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ComboItem::class);
    }
}
