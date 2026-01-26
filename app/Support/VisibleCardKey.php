<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Helper class to generate standardized lookup keys for cards
 * based on visible set code and card number.
 */
class VisibleCardKey
{
    /**
     * Generate a standardized lookup key for a card.
     *
     * Format: "SETCODE NNN/TTT"
     * Example: "SV04 053/262", "151 001/151"
     *
     * @param string|null $setCode Set/expansion code (will be normalized to uppercase)
     * @param string|null $numberRaw Raw card number as string (e.g., "053", "53", or "053/262")
     * @param int|null $totalCards Total number of cards in the set (for padding)
     * @return string|null Returns null if required data is missing
     */
    public static function make(
        ?string $setCode,
        ?string $numberRaw,
        ?int $totalCards = null
    ): ?string {
        // Validate required inputs
        if (empty($setCode) || empty($numberRaw)) {
            return null;
        }

        // Normalize set code
        $setToken = strtoupper(trim($setCode));
        $rawNumber = trim($numberRaw);

        // Parse card number - may be "053/262", "53/262", or just "53"
        if (strpos($rawNumber, '/') !== false) {
            // Already has format "NNN/TTT" - use as is, but pad if numeric
            [$cardNum, $totalInNumber] = explode('/', $rawNumber, 2);
            $cardNum = trim($cardNum);
            $totalInNumber = trim($totalInNumber);
            
            // Pad card number to 3 digits if numeric
            if (ctype_digit($cardNum)) {
                $cardNum = str_pad($cardNum, 3, '0', STR_PAD_LEFT);
            }
            
            // Pad total to 3 digits if numeric
            if (ctype_digit($totalInNumber)) {
                $totalInNumber = str_pad($totalInNumber, 3, '0', STR_PAD_LEFT);
            }
            
            $numberToken = "{$cardNum}/{$totalInNumber}";
        } else {
            // Just a card number - need to add total
            $cardNum = $rawNumber;
            
            // Pad card number to 3 digits if numeric
            if (ctype_digit($cardNum)) {
                $cardNum = str_pad($cardNum, 3, '0', STR_PAD_LEFT);
            }
            
            // Add total if provided
            if ($totalCards !== null) {
                $totalPadded = str_pad((string) $totalCards, 3, '0', STR_PAD_LEFT);
                $numberToken = "{$cardNum}/{$totalPadded}";
            } else {
                // No total available - just use padded number
                $numberToken = $cardNum;
            }
        }

        return "{$setToken} {$numberToken}";
    }
}
