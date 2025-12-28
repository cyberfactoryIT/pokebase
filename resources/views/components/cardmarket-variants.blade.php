@props(['product', 'showPrices' => true, 'compact' => false])

@php
    $variantsByType = $product->getCardmarketVariantsByType();
@endphp

@if($variantsByType->isEmpty())
    <div class="text-sm text-gray-500 italic">
        No EU prices available
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
                                    <div class="flex flex-wrap items-center gap-3 @if($compact) text-sm @endif">
                                        <div>
                                            <span class="text-gray-600">Avg:</span>
                                            <span class="font-semibold text-green-700">€{{ number_format($priceQuote->avg, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Low:</span>
                                            <span class="font-semibold text-blue-700">€{{ number_format($priceQuote->low, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Trend:</span>
                                            <span class="font-semibold text-purple-700">€{{ number_format($priceQuote->trend, 2) }}</span>
                                        </div>
                                    </div>
                                    
                                    @if(!$compact && $variant->url)
                                        <a href="{{ $variant->url }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center mt-2 text-xs text-blue-600 hover:text-blue-800">
                                            View on Cardmarket
                                            <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-500 italic">
                                        Price not available
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
        
        @if(!$compact)
            <div class="pt-2 border-t">
                <a href="https://www.cardmarket.com/en/Pokemon" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View all on Cardmarket →
                </a>
            </div>
        @endif
    </div>
@endif
