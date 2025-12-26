<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Services\DeepLTranslationService;

class Article extends Model
{
    protected $fillable = [
        'game_id',
        'original_locale',
        'category',
        'title',
        'image_path',
        'excerpt',
        'body',
        'external_url',
        'is_published',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get the game that owns the article.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get all translations for this article.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    /**
     * Get translation for a specific locale.
     */
    public function getTranslation(string $locale): ?ArticleTranslation
    {
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get or create translation for a specific locale.
     */
    public function getOrCreateTranslation(string $locale): ArticleTranslation
    {
        // If requesting original locale, return null (use original fields)
        if ($locale === $this->original_locale) {
            return null;
        }

        $translation = $this->getTranslation($locale);

        if (!$translation) {
            $translation = $this->createTranslation($locale);
        }

        return $translation;
    }

    /**
     * Create a new translation using DeepL.
     */
    public function createTranslation(string $targetLocale): ArticleTranslation
    {
        $translationService = app(DeepLTranslationService::class);

        $translations = $translationService->translateBatch([
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
        ], $targetLocale, $this->original_locale);

        return $this->translations()->create([
            'locale' => $targetLocale,
            'title' => $translations['title'] ?? $this->title,
            'excerpt' => $translations['excerpt'] ?? $this->excerpt,
            'body' => $translations['body'] ?? $this->body,
            'is_auto_translated' => true,
            'translated_at' => now(),
        ]);
    }

    /**
     * Get title in specified locale.
     */
    public function getTitleInLocale(string $locale): string
    {
        if ($locale === $this->original_locale) {
            return $this->title;
        }

        $translation = $this->getOrCreateTranslation($locale);
        return $translation ? $translation->title : $this->title;
    }

    /**
     * Get excerpt in specified locale.
     */
    public function getExcerptInLocale(string $locale): string
    {
        if ($locale === $this->original_locale) {
            return $this->excerpt;
        }

        $translation = $this->getOrCreateTranslation($locale);
        return $translation ? $translation->excerpt : $this->excerpt;
    }

    /**
     * Get body in specified locale.
     */
    public function getBodyInLocale(string $locale): string
    {
        if ($locale === $this->original_locale) {
            return $this->body;
        }

        $translation = $this->getOrCreateTranslation($locale);
        return $translation ? $translation->body : $this->body;
    }

    /**
     * Get body HTML in specified locale.
     */
    public function getBodyHtmlInLocale(string $locale): string
    {
        $body = $this->getBodyInLocale($locale);
        return $this->parseMarkdown($body);
    }

    /**
     * Check if article is in original language for given locale.
     */
    public function isOriginalLocale(string $locale): bool
    {
        return $this->original_locale === $locale;
    }

    /**
     * Scope to filter articles for the current game.
     */
    public function scopeForCurrentGame(Builder $query): Builder
    {
        return $query->where('game_id', currentGameId());
    }

    /**
     * Scope to filter only published articles.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Get the article's body rendered as HTML (from Markdown).
     */
    public function getBodyHtmlAttribute(): string
    {
        // Basic Markdown to HTML conversion (safe subset)
        return $this->parseMarkdown($this->body);
    }

    /**
     * Simple and safe Markdown parser.
     */
    public function parseMarkdown(string $text): string
    {
        // Escape HTML first
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Headers (## Header)
        $text = preg_replace('/^## (.+)$/m', '<h2 class="text-xl font-bold mt-4 mb-2">$1</h2>', $text);
        $text = preg_replace('/^### (.+)$/m', '<h3 class="text-lg font-semibold mt-3 mb-2">$1</h3>', $text);

        // Bold (**text**)
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);

        // Italic (*text*)
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);

        // Links [text](url)
        $text = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" class="text-blue-600 hover:underline">$1</a>', $text);

        // Unordered lists (- item)
        $text = preg_replace_callback('/^- (.+)$/m', function($matches) {
            static $inList = false;
            $result = '';
            if (!$inList) {
                $result .= '<ul class="list-disc list-inside my-2">';
                $inList = true;
            }
            $result .= '<li>' . $matches[1] . '</li>';
            return $result;
        }, $text);
        
        // Close any open lists
        if (strpos($text, '<ul') !== false) {
            $text .= '</ul>';
        }

        // Paragraphs (double newlines)
        $text = preg_replace('/\n\n/', '</p><p class="mb-2">', $text);
        $text = '<p class="mb-2">' . $text . '</p>';

        // Clean up extra tags
        $text = str_replace('<p class="mb-2"></p>', '', $text);

        return $text;
    }
}

