<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'thumbnail',
        'name',
        'slug',
        'price',
        'description',
        'status',
        'views',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Active images only (status=1), ordered by display order.
     */
    public function activeImages()
    {
        return $this->hasMany(ProductImage::class)
            ->where('status', 1)
            ->orderBy('display', 'asc');
    }

    /**
     * Active variants only (status=1).
     */
    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)
            ->where('status', 1);
    }

    public function recommendations()
    {
        return $this->hasMany(ProductRecommendation::class);
    }

    public function recommendedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_recommendations', 'product_id', 'recommended_product_id')
            ->withPivot('type')
            ->withTimestamps();
    }
}
