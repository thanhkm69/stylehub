<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'order_code',
        'status',
        'payment_method',
        'payment_status',
        'subtotal_amount',
        'discount_amount',
        'shipping_fee',
        'total_amount',
        'shipping_info',
        'note',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancelled_reason',
    ];

    protected $casts = [
        'shipping_info' => 'array',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    // Helper để lấy địa chỉ đầy đủ từ JSON
    public function getShippingFullAddressAttribute(): string
    {
        $info = $this->shipping_info;
        return ($info['address'] ?? '') . ', ' . 
               ($info['ward'] ?? '') . ', ' . 
               ($info['district'] ?? '') . ', ' . 
               ($info['province'] ?? '');
    }

    // Magic Accessors để code cũ (Email, Controller khác) không bị lỗi
    public function getShippingNameAttribute() { return ($this->shipping_info['name'] ?? null); }
    public function getShippingPhoneAttribute() { return ($this->shipping_info['phone'] ?? null); }
    public function getShippingEmailAttribute() { return ($this->shipping_info['email'] ?? null); }
    public function getShippingAddressAttribute() { return ($this->shipping_info['address'] ?? null); }
    public function getShippingProvinceAttribute() { return ($this->shipping_info['province'] ?? null); }
    public function getShippingDistrictAttribute() { return ($this->shipping_info['district'] ?? null); }
    public function getShippingWardAttribute() { return ($this->shipping_info['ward'] ?? null); }
}
