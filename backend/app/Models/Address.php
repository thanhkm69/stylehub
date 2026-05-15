<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'province_id',
        'province_name',
        'district_id',
        'district_name',
        'ward_code',
        'ward_name',
        'address',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'province_id' => 'integer',
        'district_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saving(function ($address) {
            if ($address->is_default) {
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
