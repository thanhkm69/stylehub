<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'flash_sale_id',
        'product_id',
        'product_variant_id',
        'discount_type',
        'discount_value',
        'original_price',
        'sale_price',
        'status',
        'display',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'original_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'status'         => 'boolean',
    ];

    // ================= RELATIONSHIPS =================

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class, 'flash_sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
