<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = [
        'title',
        'date',
        'source',
        'source_url',
        'reason_to_care',
        'content',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
