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
                'cardmarketMapping',
                'cardmarketVariants.latestPriceQuote',
                'rapidapiCard'
            ])
            ->firstOrFail();

        // Get card image with fallbacks
        $imageUrl = $this->getCardImage($card);

        // Get latest price if available
        $latestPrice = $card->prices->first();

        return view('tcg.cards.show', compact('card', 'imageUrl', 'latestPrice'));
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
}
