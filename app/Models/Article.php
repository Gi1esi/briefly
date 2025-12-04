<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'date',
        'source',
        'source_url',
        'content',
        'image_url',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
    public function userRating()
    {
        return $this->hasOne(Rating::class);
    }
    public function userFlag()
    {
        return $this->hasOne(ArticleFlag::class)->where('user_id', auth()->id());
    }


}
