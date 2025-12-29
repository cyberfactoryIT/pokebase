@props(['product', 'showPrices' => true, 'compact' => false])

@php
    $variantsByType = $product->getCardmarketVariantsByType();
@endphp

@if($variantsByType->isEmpty())
    <div class="text-sm text-gray-400 italic">
        {{ __('variants.no_eu_prices') }}
    </div>
@else
    <div class="space-y-3">
        @foreach($variantsByType as $type => $variants)
            @php
                // Calcola min e max per tutti i prezzi delle varianti
                $allPrices = [];
                foreach($variants as $variant) {
                    $priceQuote = $variant->latestPriceQuote;
                    if ($priceQuote) {
                        if ($priceQuote->low > 0) $allPrices[] = $priceQuote->low;
                        if ($priceQuote->avg > 0) $allPrices[] = $priceQuote->avg;
                        if ($priceQuote->trend > 0) $allPrices[] = $priceQuote->trend;
                        if ($priceQuote->avg7 > 0) $allPrices[] = $priceQuote->avg7;
                        if ($priceQuote->avg30 > 0) $allPrices[] = $priceQuote->avg30;
                        if ($priceQuote->low_holo > 0) $allPrices[] = $priceQuote->low_holo;
                        if ($priceQuote->avg_holo > 0) $allPrices[] = $priceQuote->avg_holo;
                        if ($priceQuote->trend_holo > 0) $allPrices[] = $priceQuote->trend_holo;
                    }
                }
                $minPrice = !empty($allPrices) ? min($allPrices) : null;
                $maxPrice = !empty($allPrices) ? max($allPrices) : null;
                $firstVariant = $variants->first();
            @endphp
            
            <div class="border border-white/10 rounded-lg p-4 bg-black/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <x-variant-badge :variant="$firstVariant" />
                        <span class="text-sm text-gray-400">{{ $variants->count() }} {{ $variants->count() > 1 ? __('variants.variants') : __('variants.variant') }}</span>
                    </div>
                    
                    @if($showPrices && $minPrice !== null && $maxPrice !== null)
                        <div class="text-right">
                            @if($minPrice == $maxPrice)
                                <div class="text-lg font-bold text-emerald-400">€{{ number_format($minPrice, 2) }}</div>
                            @else
                                <div class="text-sm text-gray-400">{{ __('variants.price_range') }}</div>
                                <div class="text-lg font-bold text-emerald-400">
                                    €{{ number_format($minPrice, 2) }} - €{{ number_format($maxPrice, 2) }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
