<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Conversation extends Pivot
{
    use HasUuid;
    protected $fillable = [
        'user_id',
        'article_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
