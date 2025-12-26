<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DeepLTranslationService
{
    private string $apiKey;
    private string $apiUrl;
    private bool $enabled;

    public function __construct()
    {
        $this->apiKey = config('services.deepl.api_key', '');
        $this->apiUrl = config('services.deepl.api_url', 'https://api-free.deepl.com/v2');
        $this->enabled = config('services.deepl.enabled', false);
    }

    /**
     * Translate text from source locale to target locale
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale = null): ?string
    {
        if (!$this->enabled || empty($this->apiKey)) {
            Log::warning('DeepL translation disabled or no API key configured');
            return $this->mockTranslation($text, $targetLocale);
        }

        // Map Laravel locales to DeepL language codes
        $targetLang = $this->mapLocaleToDeepL($targetLocale);
        $sourceLang = $sourceLocale ? $this->mapLocaleToDeepL($sourceLocale) : null;

        // Cache key for this translation
        $cacheKey = 'deepl_translation_' . md5($text . $targetLang . ($sourceLang ?? ''));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($text, $targetLang, $sourceLang) {
            try {
                $params = [
                    'auth_key' => $this->apiKey,
                    'text' => $text,
                    'target_lang' => strtoupper($targetLang),
                ];

                if ($sourceLang) {
                    $params['source_lang'] = strtoupper($sourceLang);
                }

                $response = Http::post($this->apiUrl . '/translate', $params);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['translations'][0]['text'] ?? null;
                }

                Log::error('DeepL API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('DeepL translation exception', [
                    'message' => $e->getMessage(),
                    'text_length' => strlen($text)
                ]);

                return null;
            }
        });
    }

    /**
     * Translate multiple texts at once (batch)
     */
    public function translateBatch(array $texts, string $targetLocale, string $sourceLocale = null): array
    {
        $translations = [];

        foreach ($texts as $key => $text) {
            $translations[$key] = $this->translate($text, $targetLocale, $sourceLocale);
        }

        return $translations;
    }

    /**
     * Check if DeepL is available and working
     */
    public function isAvailable(): bool
    {
        if (!$this->enabled || empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http::post($this->apiUrl . '/translate', [
                'auth_key' => $this->apiKey,
                'text' => 'test',
                'target_lang' => 'EN',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('DeepL availability check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get usage statistics from DeepL
     */
    public function getUsage(): ?array
    {
        if (!$this->enabled || empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::post($this->apiUrl . '/usage', [
                'auth_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('DeepL usage check failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Map Laravel locale codes to DeepL language codes
     */
    private function mapLocaleToDeepL(string $locale): string
    {
        $mapping = [
            'en' => 'EN',
            'it' => 'IT',
            'da' => 'DA',
            'de' => 'DE',
            'fr' => 'FR',
            'es' => 'ES',
            'pt' => 'PT',
            'nl' => 'NL',
            'pl' => 'PL',
            'ru' => 'RU',
            'ja' => 'JA',
            'zh' => 'ZH',
        ];

        return $mapping[$locale] ?? strtoupper($locale);
    }

    /**
     * Mock translation for development/testing when DeepL is not configured
     */
    private function mockTranslation(string $text, string $targetLocale): string
    {
        $prefixes = [
            'en' => '[EN]',
            'it' => '[IT]',
            'da' => '[DA]',
        ];

        $prefix = $prefixes[$targetLocale] ?? '[TRANSLATED]';
        
        return $prefix . ' ' . $text;
    }

    /**
     * Clear translation cache for specific text or all
     */
    public function clearCache(?string $text = null): void
    {
        if ($text) {
            foreach (['en', 'it', 'da'] as $locale) {
                $cacheKey = 'deepl_translation_' . md5($text . $this->mapLocaleToDeepL($locale));
                Cache::forget($cacheKey);
            }
        } else {
            // Clear all translation cache (requires cache tagging)
            Cache::flush(); // Use with caution
        }
    }
}
