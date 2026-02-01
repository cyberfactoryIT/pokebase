<!-- Top Valuable Cards Section - TCGDEX -->
@if($topCards && $topCards->isNotEmpty())
<div class="bg-white/5 border border-white/10 rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="font-semibold text-xl text-white flex items-center gap-3">
            <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
            </svg>
            {{ __('dashboard.top_valuable_cards') }}
        </h3>
    </div>
    
    <div class="space-y-4">
        @foreach($topCards as $index => $item)
        @php
            $card = $item->tcgdexCard;
            $cardName = is_array($card->name) ? ($card->name['en'] ?? $card->tcgdex_id) : $card->name;
            $cardImage = $card->image_small_url . '/high.webp';
            $priceEur = $item->cached_price;
            $totalValue = $priceEur ? $priceEur * $item->quantity : null;
            
            // Convert to user's preferred currency
            $user = Auth::user();
            $preferredCurrency = $user->preferred_currency ?? 'EUR';
            $displayPrice = $priceEur;
            $currencySymbol = '€';
            
            if ($preferredCurrency && $preferredCurrency !== 'EUR' && $priceEur) {
                $displayPrice = \App\Services\CurrencyService::convert($priceEur, 'EUR', $preferredCurrency);
                $currencySymbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
            }
            
            $displayTotal = $totalValue;
            if ($preferredCurrency && $preferredCurrency !== 'EUR' && $totalValue) {
                $displayTotal = \App\Services\CurrencyService::convert($totalValue, 'EUR', $preferredCurrency);
            }
        @endphp
        <a href="{{ route('pokemon.card', $card->tcgdex_id) }}" 
           class="group flex items-center gap-4 p-4 bg-white/5 border border-white/10 rounded-lg hover:border-yellow-500/50 hover:bg-white/10 transition-all">
            
            <!-- Card Image Thumbnail -->
            <div class="flex-shrink-0 w-16 h-20 bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg overflow-hidden border border-white/10">
                @if($cardImage)
                    <img src="{{ $cardImage }}" 
                         alt="{{ $cardName }}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                         loading="lazy">
                @else
                    <div class="flex items-center justify-center h-full text-gray-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- Card Details -->
            <div class="flex-grow min-w-0">
                <h4 class="font-semibold text-white mb-1 truncate group-hover:text-yellow-400 transition">
                    {{ $cardName }}
                </h4>

                <!-- Quantity & Total Value -->
                <div class="flex items-center gap-3 mt-2">
                    <span class="px-2 py-1 bg-white/5 rounded text-sm">
                        {{ $item->quantity }}x
                    </span>
                    
                </div>
            </div>
            
            <!-- Price on the right -->
            <div class="flex-shrink-0 text-right">
                @if($displayPrice)
                    <p class="text-lg font-bold text-green-400">
                        @if(in_array($preferredCurrency, ['DKK', 'SEK', 'NOK']))
                            {{ number_format($displayPrice, 2, ',', '.') }} {{ $currencySymbol }}
                        @else
                            {{ $currencySymbol }}{{ number_format($displayPrice, 2, '.', ',') }}
                        @endif
                    </p>
                    @if($preferredCurrency !== 'EUR')
                    <p class="text-xs text-gray-500">(€{{ number_format($priceEur, 2) }})</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500">{{ __('dashboard.price_unavailable') }}</p>
                @endif
            </div>
            
            <!-- Arrow Icon -->
            <svg class="w-5 h-5 text-gray-600 group-hover:text-yellow-400 group-hover:translate-x-1 transition-all" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
        @endforeach
    </div>
    
    <!-- Analytics Link -->
    <div class="mt-6 pt-6 border-t border-white/10">
        <a href="{{ route('collection.index') }}" 
           class="flex items-center justify-center gap-2 w-full py-3 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-500 hover:to-orange-500 text-white font-semibold rounded-lg transition-all group">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span>{{ __('dashboard.view_collection_analytics') }}</span>
        </a>
    </div>
</div>
@endif
