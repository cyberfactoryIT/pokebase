# Articles Multi-Language System

## Overview
Articles are automatically translated from their original language to all supported languages using the DeepL API.

**Supported Languages:**
- English (en)
- Italian (it)
- Danish (da)

## Features
- **Original Language Selection**: When creating/editing an article, select which language you're writing in
- **Automatic Translation**: Articles are lazily translated when a user views them in a different language
- **Translation Caching**: Translations are cached for 30 days to minimize API usage
- **Language Badges**: Dashboard shows which articles are original vs translated
- **Mock Mode**: Development mode that prefixes content with `[EN]`, `[IT]`, `[DA]` instead of calling API

## Setup

### 1. Enable DeepL API
Add to your `.env` file:

```env
DEEPL_API_KEY=your-api-key-here
DEEPL_ENABLED=true
DEEPL_API_URL=https://api-free.deepl.com/v2
```

**Get API Key:**
1. Sign up at https://www.deepl.com/pro-api
2. Free tier: 500,000 characters/month
3. Paid tier: €4.99/month for more characters

### 2. Without API (Mock Mode)
If `DEEPL_ENABLED=false` or no API key is set, the system uses mock translations:
- Prefixes content with language code: `[EN]`, `[IT]`, `[DA]`
- No API calls made
- Perfect for development/testing

## Usage

### Admin Interface
1. **Create Article**: Go to `/superadmin/articles/create`
2. **Select Original Language**: Choose EN, IT, or DA (language you're writing in)
3. **Write Content**: Enter title, excerpt, body in your selected language
4. **Publish**: Translations are generated on-demand when users view the article

### Manual Translation Command
Pre-generate translations for all articles:

```bash
# Translate all articles to Italian
php artisan articles:translate --locale=it

# Translate all articles to all languages
php artisan articles:translate --all

# Translate specific article
php artisan articles:translate --locale=da --article=1

# Regenerate existing translations
php artisan articles:translate --all --force
```

### Frontend Display
- **Dashboard**: Shows language badge on each article
  - Green badge = Original language
  - Yellow badge = Translated (shows "IT ← EN" format)
- **Auto-Translation**: When user views article in different language, translation is created automatically
- **User Locale**: Uses logged-in user's `locale` preference, or falls back to `app()->getLocale()`

## Database Structure

### `articles` table
- `original_locale` (varchar): Language article was written in (en, it, da)

### `article_translations` table
- `article_id`: Foreign key to articles
- `locale`: Target language (en, it, da)
- `title`: Translated title
- `excerpt`: Translated excerpt
- `body`: Translated body (Markdown)
- `is_auto_translated`: Boolean (always true for DeepL)
- `translated_at`: Timestamp
- Unique constraint on `(article_id, locale)`

## API Usage

### Translation Service
Located in `app/Services/DeepLTranslationService.php`

```php
use App\Services\DeepLTranslationService;

$service = app(DeepLTranslationService::class);

// Single text translation
$translated = $service->translate('Hello world', 'it', 'en');

// Batch translation
$translations = $service->translateBatch([
    'title' => 'My Title',
    'body' => 'My content',
], 'da', 'en');

// Check if available
if ($service->isAvailable()) {
    // API is configured and enabled
}

// Get usage stats
$usage = $service->getUsage();
// ['character_count' => 1500, 'character_limit' => 500000]
```

### Article Model Methods
```php
$article = Article::find(1);

// Get translation (returns null if not exists)
$translation = $article->getTranslation('it');

// Get or create translation (creates via DeepL if needed)
$translation = $article->getOrCreateTranslation('it');

// Manually create translation
$translation = $article->createTranslation('da');

// Get translated content
$title = $article->getTitleInLocale('it');
$excerpt = $article->getExcerptInLocale('it');
$body = $article->getBodyInLocale('it');
$bodyHtml = $article->getBodyHtmlInLocale('it'); // Markdown parsed

// Check if locale is original
if ($article->isOriginalLocale('en')) {
    // Show "Original" badge
}
```

## Costs & Limits

### DeepL Free Tier
- 500,000 characters/month
- $0 cost
- Perfect for most small-medium sites

### Example Usage
- Average article: ~2,000 characters (title + excerpt + body)
- Translation: 2,000 chars × 2 languages = 4,000 chars
- Capacity: 500,000 ÷ 4,000 = 125 articles/month
- With caching: Translations are reused, so ongoing cost is only for new articles

### DeepL Pro
- €4.99/month for 1M characters
- €19.99/month for 10M characters
- Higher quality, faster processing

## Cache Management

Translations are cached for 30 days. To clear:

```bash
# Clear all cache
php artisan cache:clear

# Or specific key pattern
php artisan tinker
> Cache::forget('deepl_translation_' . md5('text_locale'));
```

## Best Practices

1. **Write in Native Language**: Always set `original_locale` correctly
2. **Review Translations**: While DeepL is high-quality, review important articles
3. **Pre-translate Popular Articles**: Use command to pre-generate before traffic surge
4. **Monitor API Usage**: Check usage in DeepL dashboard
5. **Cache Considerations**: Cached for 30 days, so updates require cache clear or `--force`

## Troubleshooting

### Translations Not Appearing
1. Check `.env` has `DEEPL_ENABLED=true`
2. Verify API key is valid
3. Check `article_translations` table for records
4. Clear cache: `php artisan cache:clear`

### API Quota Exceeded
1. Check usage: `$service->getUsage()`
2. Upgrade to paid plan
3. Reduce translation frequency
4. Use `--force` sparingly

### Wrong Language Displayed
1. Check user's `locale` field in `users` table
2. Verify `app.locale` in `config/app.php`
3. Check `original_locale` on article is correct

## Future Enhancements
- Manual translation override (admin can edit translations)
- Translation history/versioning
- Support for more languages
- Alternative translation services (Google Translate, AWS Translate)
- Translation quality scoring
- A/B testing for translation quality
