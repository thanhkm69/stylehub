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

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::saved(function ($product) {
            // Clear cache
            \Illuminate\Support\Facades\Cache::forget('product_' . $product->slug);
            \Illuminate\Support\Facades\Cache::forget('home_data');

            // Dispatch job to generate recommendations
            if ($product->status == 1) {
                dispatch(new \App\Jobs\GenerateProductRecommendationsJob($product->id));
            }
        });

        static::deleted(function ($product) {
            // Clear cache
            \Illuminate\Support\Facades\Cache::forget('product_' . $product->slug);
            \Illuminate\Support\Facades\Cache::forget('home_data');

            // Clear old recommendations in recommendations table
            \App\Models\ProductRecommendation::where('product_id', $product->id)
                ->orWhere('recommended_product_id', $product->id)
                ->delete();
        });
    }


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
