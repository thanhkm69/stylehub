<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'blog_category_id', 'title', 'slug', 'summary', 'content',
        'image', 'status', 'meta_title', 'meta_description',
        'meta_keywords', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }
}
