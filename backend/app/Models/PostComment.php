<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostComment extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'status',
        'moderation_status',
        'moderation_reason',
        'moderation_categories',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'moderation_categories' => 'array',
            'moderated_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
