<?php
/**
 * app/helpers.php
 *
 * Helper functions for contextual help entries stored in DB.
 * Works with App\Support\HelpRegistry and App\Models\Help (JSON i18n fields).
 *
 * Usage examples:
 *   $entry = help('security.2fa');           // array or null
 *   echo help_short('security.2fa');         // short text (string|null)
 *   echo help_html_long('security.2fa');     // long text rendered as HTML from Markdown
 *   echo help_render('security.2fa');        // rendered HTML of the <x-help> component
 */

declare(strict_types=1);

use App\Support\HelpRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if (! function_exists('help')) {
    /**
     * Fetch a help entry resolved for the given (or current) locale.
     *
     * @param  string      $key     Help key, e.g. "security.2fa"
     * @param  string|null $locale  Locale override, otherwise app()->getLocale()
     * @return array<string,mixed>|null  ['key','icon','title','short','long','links','meta'] or null if not found/active
     */
    function help(string $key, ?string $locale = null): ?array
    {
        /** @var HelpRegistry $registry */
        $registry = app(HelpRegistry::class);
        return $registry->get($key, $locale);
    }
}

if (! function_exists('help_title')) {
    /**
     * Get the localized title of a help entry.
     */
    function help_title(string $key, ?string $locale = null): ?string
    {
        return Arr::get(help($key, $locale), 'title');
    }
}

if (! function_exists('help_short')) {
    /**
     * Get the localized short text of a help entry.
     */
    function help_short(string $key, ?string $locale = null): ?string
    {
        return Arr::get(help($key, $locale), 'short');
    }
}

if (! function_exists('help_long')) {
    /**
     * Get the localized long (Markdown) text of a help entry.
     */
    function help_long(string $key, ?string $locale = null): ?string
    {
        return Arr::get(help($key, $locale), 'long');
    }
}

if (! function_exists('help_icon')) {
    /**
     * Get the icon name of a help entry (e.g. "shield-check").
     */
    function help_icon(string $key, ?string $locale = null): ?string
    {
        return Arr::get(help($key, $locale), 'icon');
    }
}

if (! function_exists('help_links')) {
    /**
     * Get the links array of a help entry (route/url + localized label).
     *
     * @return array<int, array<string,mixed>>
     */
    function help_links(string $key, ?string $locale = null): array
    {
        return Arr::get(help($key, $locale), 'links', []) ?? [];
    }
}

if (! function_exists('help_meta')) {
    /**
     * Get arbitrary meta data from a help entry.
     *
     * @return array<string,mixed>
     */
    function help_meta(string $key, ?string $locale = null): array
    {
        return Arr::get(help($key, $locale), 'meta', []) ?? [];
    }
}

if (! function_exists('help_html_long')) {
    /**
     * Render the long Markdown text to safe HTML (server-side).
     * Note: uses Str::markdown(); add HTML purifier if you allow raw HTML in content.
     */
    function help_html_long(string $key, ?string $locale = null): string
    {
        $md = help_long($key, $locale);
        if (! $md) {
            return '';
        }

        // Convert Markdown to HTML; returns HtmlString safe for Blade echoing with {!! !!}
        $html = Str::of($md)->markdown()->toHtmlString();

        // When returning as string here, we ensure it's plain string (for non-Blade contexts).
        return (string) $html;
    }
}

if (! function_exists('help_render')) {
    /**
     * Render the Blade component for a help entry and return HTML.
     * Useful when you are in PHP (controller/service) and need the component markup.
     *
     * @param  string $key
     * @param  array<string,mixed> $props  Extra props passed to the component view (e.g., ['locale' => 'it'])
     * @return string
     */
    function help_render(string $key, array $props = []): string
    {
        // The component expects 'entry' or 'key'; we pass the key so the component resolves it.
        $data = array_merge(['key' => $key], $props);

        // If you prefer to bypass the component and inject the resolved entry:
        // $data = array_merge(['entry' => help($key, $props['locale'] ?? null)], $props);

        return (string) view('components.help', $data)->render();
    }
}

// ============================================================================
// Cardmarket Price Formatting Helpers
// ============================================================================

if (! function_exists('formatCardmarketPrice')) {
    /**
     * Format a Cardmarket price with Euro symbol and 2 decimals
     *
     * @param  float|null  $amount
     * @return string
     */
    function formatCardmarketPrice(?float $amount): string
    {
        if ($amount === null || $amount <= 0) {
            return '—';
        }
        
        return '€' . number_format($amount, 2, '.', ',');
    }
}

if (! function_exists('comparePrice')) {
    /**
     * Compare TCGCSV (USD) and Cardmarket (EUR) prices
     * Returns array with difference percentage and which is cheaper
     *
     * @param  float|null  $tcgcsvPrice  Price in USD
     * @param  float|null  $cardmarketPrice  Price in EUR
     * @param  float  $exchangeRate  USD to EUR conversion rate (default ~0.92)
     * @return array{difference: float|null, cheaper: string|null, tcgcsv_eur: float|null}
     */
    function comparePrice(?float $tcgcsvPrice, ?float $cardmarketPrice, float $exchangeRate = 0.92): array
    {
        if ($tcgcsvPrice === null || $cardmarketPrice === null || $tcgcsvPrice <= 0 || $cardmarketPrice <= 0) {
            return [
                'difference' => null,
                'cheaper' => null,
                'tcgcsv_eur' => null,
            ];
        }
        
        // Convert USD to EUR for comparison
        $tcgcsvInEur = $tcgcsvPrice * $exchangeRate;
        
        // Calculate percentage difference
        $difference = (($cardmarketPrice - $tcgcsvInEur) / $tcgcsvInEur) * 100;
        
        // Determine which is cheaper (only if difference > 10%)
        $cheaper = null;
        if (abs($difference) > 10) {
            $cheaper = $difference < 0 ? 'cardmarket' : 'tcgcsv';
        }
        
        return [
            'difference' => round($difference, 1),
            'cheaper' => $cheaper,
            'tcgcsv_eur' => round($tcgcsvInEur, 2),
        ];
    }
}

if (! function_exists('convertUsdToEur')) {
    /**
     * Convert USD to EUR using approximate exchange rate
     *
     * @param  float  $amount  Amount in USD
     * @param  float  $rate  Exchange rate (default ~0.92)
     * @return float  Amount in EUR
     */
    function convertUsdToEur(float $amount, float $rate = 0.92): float
    {
        return round($amount * $rate, 2);
    }
}

if (! function_exists('formatPrice')) {
    /**
     * Format price with optional currency conversion based on user preference
     *
     * @param  float  $amount  Original amount
     * @param  string  $originalCurrency  Original currency (EUR, USD, etc.)
     * @param  \App\Models\User|null  $user  User with currency preference
     * @return string  Formatted price, e.g. "74 DKK (original price 10 €)"
     */
    function formatPrice(float $amount, string $originalCurrency, ?\App\Models\User $user = null): string
    {
        // Get user's preferred currency or use original
        $preferredCurrency = $user?->preferred_currency;
        
        return \App\Services\CurrencyService::formatPrice(
            $amount,
            $originalCurrency,
            $preferredCurrency
        );
    }
}

if (! function_exists('getUserCurrency')) {
    /**
     * Get user's preferred currency or default based on context
     *
     * @param  \App\Models\User|null  $user
     * @param  string  $defaultSource  Default source ('cardmarket' or 'tcgplayer')
     * @return string  Currency code (EUR, USD, etc.)
     */
    function getUserCurrency(?\App\Models\User $user = null, string $defaultSource = 'cardmarket'): string
    {
        if ($user?->preferred_currency) {
            return $user->preferred_currency;
        }

        return \App\Services\CurrencyService::getDefaultCurrency($defaultSource);
    }
}
