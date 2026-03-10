<!-- Top Valuable Cards Section - CMAPI (Lorcana/One Piece) -->
@php
    $topCards = \App\Models\UserCollection::where('user_id', Auth::id())
        ->whereNotNull('cmapi_card_id')
        ->with('cmapiCard')
        ->get()
        ->sortByDesc(function($item) {
            $card = $item->cmapiCard;
            if (!$card) return 0;
            $price = $item->cached_price ?? $card->price_eur ?? 0;
            return $price * $item->quantity;
        })
        ->take(4);
@endphp

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
            $card = $item->cmapiCard;
            if (!$card) continue;
            
            $cardName = $card->name;
            $cardImage = $card->image_large_url ?? $card->image_small_url;
            $priceEur = $item->cached_price ?? $card->price_eur;
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
        <a href="/{{ $currentGame->slug }}/cards/{{ $card->cmapi_id }}" 
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

                <!-- Quantity & Rarity -->
                <div class="flex items-center gap-3 mt-2">
                    <span class="px-2 py-1 bg-white/5 rounded text-sm">
                        {{ $item->quantity }}x
                    </span>
                    @if($card->rarity)
                        <span class="text-xs text-gray-400">{{ $card->rarity }}</span>
                    @endif
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
                    @if($displayTotal && $item->quantity > 1)
                        <p class="text-xs text-gray-400 mt-1">
                            {{ __('dashboard.total') }}:
                            @if(in_array($preferredCurrency, ['DKK', 'SEK', 'NOK']))
                                {{ number_format($displayTotal, 2, ',', '.') }} {{ $currencySymbol }}
                            @else
                                {{ $currencySymbol }}{{ number_format($displayTotal, 2, '.', ',') }}
                            @endif
                        </p>
                    @endif
                @else
                    <p class="text-sm text-gray-500">{{ __('dashboard.no_price') }}</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
