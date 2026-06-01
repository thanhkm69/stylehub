<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentModerationSetting extends Model
{
    protected $fillable = [
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }
}
