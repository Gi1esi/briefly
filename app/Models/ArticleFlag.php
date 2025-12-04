<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleFlag extends Model
{
    protected $fillable = [
        'article_id',
        'user_id',
        'is_bookmarked',
        'is_read_later',
        'is_archived',
    ];

    protected $casts = [
        'is_bookmarked' => 'boolean',
        'is_read_later' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}

