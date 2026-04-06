<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'description',
        'display',
        'status',
    ];
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function childrens()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('display', 'asc')->orderBy('created_at', 'desc');
    }
    public function childrensRecursive()
    {
        return $this->childrens()->with('childrensRecursive');
    }
}
