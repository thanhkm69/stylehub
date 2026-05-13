<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecommendation extends Model
{
    protected $fillable = [
        'product_id',
        'recommended_product_id',
        'type',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function recommendedProduct()
    {
        return $this->belongsTo(Product::class, 'recommended_product_id');
    }
}
