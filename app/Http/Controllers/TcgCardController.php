<?php

namespace App\Http\Controllers;

use App\Models\TcgcsvProduct;
use Illuminate\View\View;

class TcgCardController extends Controller
{
    /**
     * Show card detail page (Scrydex-like layout)
     */
    public function show(int $productId): View
    {
        $card = TcgcsvProduct::where('product_id', $productId)
            ->with([
                'group', 
                'prices' => function($query) {
                    // Get latest price snapshot
                    $query->latest('snapshot_at')->limit(1);
                },
                'cardmarketProduct',
                'rapidapiCard',
                'tcgdxCard'
            ])
            ->firstOrFail();

        // Add user interaction flags if authenticated
        if (auth()->check()) {
            $userId = auth()->id();
            
            $card->is_liked = \DB::table('user_likes')
                ->where('user_id', $userId)
                ->where('product_id', $card->product_id)
                ->exists();
                
            $card->is_in_wishlist = \DB::table('user_wishlist_items')
                ->where('user_id', $userId)
                ->where('product_id', $card->product_id)
                ->exists();
                
            $card->is_watched = \DB::table('user_watch_items')
                ->where('user_id', $userId)
                ->where('product_id', $card->product_id)
                ->exists();
        } else {
            $card->is_liked = false;
            $card->is_in_wishlist = false;
            $card->is_watched = false;
        }

        // Get card image with fallbacks
        $imageUrl = $this->getCardImage($card);

        // Get latest price if available
        $latestPrice = $card->prices->first();

        // Prepare price history for chart (last 30 days)
        $priceHistory = $this->preparePriceHistory($card);

        return view('tcg.cards.show', compact('card', 'imageUrl', 'latestPrice', 'priceHistory'));
    }

    /**
     * Get card image URL with fallbacks
     */
    private function getCardImage($card): ?string
    {
        // Try direct field
        if (!empty($card->image_url)) {
            return $card->image_url;
        }

        // Try raw JSON variations
        if (!empty($card->raw)) {
            $raw = $card->raw;
            
            // Common variations
            if (!empty($raw['imageUrl'])) return $raw['imageUrl'];
            if (!empty($raw['image_url'])) return $raw['image_url'];
            if (!empty($raw['images']['large'])) return $raw['images']['large'];
            if (!empty($raw['images']['small'])) return $raw['images']['small'];
            if (!empty($raw['image'])) return $raw['image'];
        }

        return null;
    }

    /**
     * Prepare price history data for chart
     */
    private function preparePriceHistory($card): array
    {
        $cardmarketProductId = $card->cardmarket_product_id;
        
        if (!$cardmarketProductId) {
            return ['trend' => [], 'trend_holo' => []];
        }

        // Get last 30 days of quotes
        $quotes = \App\Models\CardmarketPriceQuote::where('cardmarket_product_id', $cardmarketProductId)
            ->where('as_of_date', '>=', now()->subDays(30))
            ->orderBy('as_of_date', 'asc')
            ->get();

        if ($quotes->isEmpty()) {
            return ['trend' => [], 'trend_holo' => []];
        }

        // Calculate days of data we have
        $firstQuoteDate = $quotes->first()->as_of_date;
        $daysOfData = now()->startOfDay()->diffInDays($firstQuoteDate);

        // Build trend data
        $trendData = [];
        $trendHoloData = [];

        foreach ($quotes as $quote) {
            if ($quote->trend !== null) {
                $trendData[] = [
                    'x' => $quote->as_of_date->format('Y-m-d'),
                    'y' => (float) $quote->trend
                ];
            }
            
            if ($quote->trend_holo !== null) {
                $trendHoloData[] = [
                    'x' => $quote->as_of_date->format('Y-m-d'),
                    'y' => (float) $quote->trend_holo
                ];
            }
        }

        // Get latest quote for avg7 and avg30
        $latestQuote = $quotes->last();

        // Add synthetic points based on data availability
        if ($daysOfData < 7) {
            // Less than 7 days: add avg7 at -7d and avg30 at -30d
            if ($latestQuote->avg7 !== null) {
                $trendData[] = [
                    'x' => now()->subDays(7)->format('Y-m-d'),
                    'y' => (float) $latestQuote->avg7
                ];
            }
            if ($latestQuote->avg30 !== null) {
                $trendData[] = [
                    'x' => now()->subDays(30)->format('Y-m-d'),
                    'y' => (float) $latestQuote->avg30
                ];
            }
            
            // Add holo synthetic points if available
            if ($latestQuote->avg7_holo !== null) {
                $trendHoloData[] = [
                    'x' => now()->subDays(7)->format('Y-m-d'),
                    'y' => (float) $latestQuote->avg7_holo
                ];
            }
            if ($latestQuote->avg30_holo !== null) {
                $trendHoloData[] = [
                    'x' => now()->subDays(30)->format('Y-m-d'),
                    'y' => (float) $latestQuote->avg30_holo
                ];
            }
        } elseif ($daysOfData < 30) {
            // Between 7 and 30 days: add only avg30 at -30d
            if ($latestQuote->avg30 !== null) {
                $trendData[] = [
                    'x' => now()->subDays(30)->format('Y-m-d'),
                    'y' => (float) $latestQuote->avg30
                ];
            }
            
            // Add holo synthetic point if available
            if ($latestQuote->avg30_holo !== null) {
                $trendHoloData[] = [
                    'x' => now()->subDays(30)->format('Y-m-d'),
                    'y' => (float) $latestQuote->avg30_holo
                ];
            }
        }

        // Sort trend data by date
        usort($trendData, fn($a, $b) => strcmp($a['x'], $b['x']));
        usort($trendHoloData, fn($a, $b) => strcmp($a['x'], $b['x']));

        return [
            'trend' => $trendData,
            'trend_holo' => $trendHoloData
        ];
    }
}
