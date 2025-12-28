@props(['product', 'showPrices' => true, 'compact' => false])

@php
    $variantsByType = $product->getCardmarketVariantsByType();
@endphp

@if($variantsByType->isEmpty())
    <div class="text-sm text-gray-400 italic">
        {{ __('variants.no_eu_prices') }}
    </div>
@else
    <div class="space-y-4">
        @foreach($variantsByType as $type => $variants)
            <div class="@if(!$compact) border rounded-lg p-4 @else border-b pb-3 last:border-b-0 @endif">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <x-variant-badge :variant="$variants->first()" />
                        @if($variants->count() > 1)
                            <span class="text-xs text-gray-500">({{ $variants->count() }} variants)</span>
                        @endif
                    </div>
                </div>
                
                @if($showPrices)
                    <div class="@if($compact) space-y-1 @else space-y-2 @endif">
                        @foreach($variants as $variant)
                            @php
                                $priceQuote = $variant->latestPriceQuote;
                            @endphp
                            
                            <div class="@if(!$compact) bg-gray-50 rounded p-3 @else py-1 @endif">
                                @if($variants->count() > 1)
                                    <div class="text-xs text-gray-600 mb-1">{{ $variant->name }}</div>
                                @endif
                                
                                @if($priceQuote)
                                    <!-- Regular Prices -->
                                    <div class="mb-3">
                                        <div class="text-xs text-gray-400 mb-1 flex items-center gap-1">
                                            <span>{{ __('variants.regular') }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 @if($compact) text-sm @endif">
                                            <div>
                                                <span class="text-gray-400">{{ __('variants.avg_price') }}:</span>
                                                <span class="font-semibold text-green-400">€{{ number_format($priceQuote->avg, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400">{{ __('variants.low_price') }}:</span>
                                                <span class="font-semibold text-blue-400">€{{ number_format($priceQuote->low, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400">{{ __('variants.trend_price') }}:</span>
                                                <span class="font-semibold text-purple-400">€{{ number_format($priceQuote->trend, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Foil/Holo Prices (if available) -->
                                    @if($priceQuote->avg_holo || $priceQuote->low_holo || $priceQuote->trend_holo)
                                    <div class="mb-3 pt-2 border-t border-white/5">
                                        <div class="text-xs text-gray-400 mb-1 flex items-center gap-1">
                                            <span>✨</span>
                                            <span>{{ __('variants.foil_holo') }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 @if($compact) text-sm @endif">
                                            @if($priceQuote->avg_holo)
                                            <div>
                                                <span class="text-gray-400">{{ __('variants.avg_price') }}:</span>
                                                <span class="font-semibold text-green-400">€{{ number_format($priceQuote->avg_holo, 2) }}</span>
                                            </div>
                                            @endif
                                            @if($priceQuote->low_holo)
                                            <div>
                                                <span class="text-gray-400">{{ __('variants.low_price') }}:</span>
                                                <span class="font-semibold text-blue-400">€{{ number_format($priceQuote->low_holo, 2) }}</span>
                                            </div>
                                            @endif
                                            @if($priceQuote->trend_holo)
                                            <div>
                                                <span class="text-gray-400">{{ __('variants.trend_price') }}:</span>
                                                <span class="font-semibold text-purple-400">€{{ number_format($priceQuote->trend_holo, 2) }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Historical Averages (if available) -->
                                    @if($priceQuote->avg7 || $priceQuote->avg30)
                                    <div class="pt-2 border-t border-white/5">
                                        <div class="flex flex-wrap items-center gap-3 text-xs">
                                            @if($priceQuote->avg7)
                                            <div class="text-gray-400">
                                                <span>{{ __('variants.avg_7d') }}:</span>
                                                <span class="text-gray-300">€{{ number_format($priceQuote->avg7, 2) }}</span>
                                            </div>
                                            @endif
                                            @if($priceQuote->avg30)
                                            <div class="text-gray-400">
                                                <span>{{ __('variants.avg_30d') }}:</span>
                                                <span class="text-gray-300">€{{ number_format($priceQuote->avg30, 2) }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if(!$compact && $variant->url)
                                        <a href="{{ $variant->url }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center mt-2 text-xs text-blue-400 hover:text-blue-300">
                                            {{ __('variants.view_on_cardmarket') }}
                                            <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-400 italic">
                                        {{ __('variants.price_not_available') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
        
        @if(!$compact)
            <div class="pt-2 border-t border-white/10">
                <a href="https://www.cardmarket.com/en/Pokemon" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center text-sm text-blue-400 hover:text-blue-300 font-medium">
                    {{ __('variants.view_all_variants') }} →
                </a>
            </div>
        @endif
    </div>
@endif
