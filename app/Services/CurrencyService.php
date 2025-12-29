<?php

namespace App\Services;

class CurrencyService
{
    /**
     * Exchange rates (base: EUR)
     * Updated: December 2025
     */
    private const EXCHANGE_RATES = [
        'EUR' => 1.0,
        'USD' => 1.05,
        'GBP' => 0.85,
        'DKK' => 7.46,
        'SEK' => 11.20,
        'NOK' => 11.50,
        'CHF' => 0.95,
        'JPY' => 155.0,
        'CAD' => 1.45,
        'AUD' => 1.65,
    ];

    /**
     * Currency symbols
     */
    private const CURRENCY_SYMBOLS = [
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'DKK' => 'kr',
        'SEK' => 'kr',
        'NOK' => 'kr',
        'CHF' => 'CHF',
        'JPY' => '¥',
        'CAD' => 'C$',
        'AUD' => 'A$',
    ];

    /**
     * Get all available currencies
     */
    public static function getCurrencies(): array
    {
        return array_keys(self::EXCHANGE_RATES);
    }

    /**
     * Get currency symbol
     */
    public static function getSymbol(string $currency): string
    {
        return self::CURRENCY_SYMBOLS[$currency] ?? $currency;
    }

    /**
     * Convert amount from one currency to another
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        // Convert from source to EUR (base)
        $fromRate = self::EXCHANGE_RATES[$from] ?? 1.0;
        $amountInEur = $amount / $fromRate;

        // Convert from EUR to target
        $toRate = self::EXCHANGE_RATES[$to] ?? 1.0;
        return $amountInEur * $toRate;
    }

    /**
     * Format price with currency conversion
     * Returns format: "74 DKK (original price 10 €)"
     */
    public static function formatPrice(
        float $amount,
        string $originalCurrency,
        ?string $preferredCurrency = null,
        int $decimals = 2
    ): string {
        // If no preferred currency, use original
        if (!$preferredCurrency || $preferredCurrency === $originalCurrency) {
            return self::formatAmount($amount, $originalCurrency, $decimals);
        }

        // Convert to preferred currency
        $convertedAmount = self::convert($amount, $originalCurrency, $preferredCurrency);
        $convertedStr = self::formatAmount($convertedAmount, $preferredCurrency, $decimals);
        $originalStr = self::formatAmount($amount, $originalCurrency, $decimals);

        return "{$convertedStr} (original price {$originalStr})";
    }

    /**
     * Format amount with currency symbol
     */
    private static function formatAmount(float $amount, string $currency, int $decimals = 2): string
    {
        $symbol = self::getSymbol($currency);
        $formatted = number_format($amount, $decimals, '.', ',');

        // For EUR, USD, GBP, JPY - symbol before
        if (in_array($currency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
            return "{$symbol}{$formatted}";
        }

        // For Nordic currencies - symbol after with space
        return "{$formatted} {$symbol}";
    }

    /**
     * Get default currency for a price source
     */
    public static function getDefaultCurrency(string $source): string
    {
        return match ($source) {
            'cardmarket' => 'EUR',
            'tcgplayer' => 'USD',
            default => 'USD',
        };
    }
}
