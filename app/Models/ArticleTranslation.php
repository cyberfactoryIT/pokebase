<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Model
{
    protected $fillable = [
        'article_id',
        'locale',
        'title',
        'excerpt',
        'body',
        'is_auto_translated',
        'translated_at',
    ];

    protected $casts = [
        'is_auto_translated' => 'boolean',
        'translated_at' => 'datetime',
    ];

    /**
     * Get the article that owns the translation.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the translation's body rendered as HTML (from Markdown).
     */
    public function getBodyHtmlAttribute(): string
    {
        return $this->article->parseMarkdown($this->body);
    }
}

