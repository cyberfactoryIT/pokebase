{{-- CMAPI Card Grid (Lorcana, One Piece) --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($deck->deckCards->where('cmapi_card_id', '!=', null) as $deckCard)
    @php
        $card = $deckCard->cmapiCard;
        if (!$card) continue;
        
        $inCollection = auth()->user()->collection()->where('cmapi_card_id', $card->cmapi_id)->exists();
        $displayImage = $card->image_large_url ?? $card->image_small_url;
    @endphp
    
    <div class="deck-card-item bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition overflow-hidden group relative" 
         data-rarity="{{ $card->rarity ?? 'Unknown' }}"
         data-card-price="0"
         data-quantity="{{ $deckCard->quantity }}"
         data-cmapi-card-id="{{ $card->cmapi_id }}">
        <!-- Quantity Badge -->
        <div class="absolute top-2 left-2 z-10 bg-blue-600/90 text-white px-2 py-1 rounded text-sm font-semibold">
            x{{ $deckCard->quantity }}
        </div>
        
        <!-- Not in Collection Badge -->
        @if(!$inCollection)
        <div class="absolute top-2 right-2 z-10">
            <form method="POST" action="{{ route('collection.add') }}" class="inline" onsubmit="event.preventDefault(); quickAddCardToCollection(null, null, '{{ addslashes($card->name) }}', this);">
                @csrf
                <input type="hidden" name="cmapi_card_id" value="{{ $card->cmapi_id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" 
                    class="p-1.5 bg-orange-600/90 hover:bg-orange-600 rounded text-white transition"
                    title="{{ __('decks/show.not_in_collection') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </form>
        </div>
        @endif
        
        <!-- Card Image -->
        <div class="aspect-[245/342] bg-black/50 overflow-hidden cursor-pointer" onclick="window.location.href='/tcg/cards/{{ $card->cmapi_id }}'">
            @if($displayImage)
            <img src="{{ $displayImage }}" alt="{{ $card->name }}" class="w-full h-full object-cover group-hover:scale-105 transition">
            @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            @endif
        </div>
        
        <!-- Card Info -->
        <div class="p-3">
            <h4 class="text-white font-semibold text-sm truncate group-hover:text-blue-400 transition cursor-pointer" onclick="window.location.href='/tcg/cards/{{ $card->cmapi_id }}'">
                {{ $card->name }}
            </h4>
            <p class="text-gray-400 text-xs truncate mt-1">
                {{ $card->set->name ?? 'Unknown Set' }}
                @if($card->number)
                · #{{ $card->number }}
                @endif
            </p>
            
            @can('seePrices')
            <!-- Price Display (Cached) -->
            @if($deckCard->cached_price && $deckCard->cached_price > 0)
                @if($preferredCurrency && $preferredCurrency !== 'EUR')
                    @php
                        $convertedPrice = \App\Services\CurrencyService::convert($deckCard->cached_price, 'EUR', $preferredCurrency);
                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                        $totalConverted = $convertedPrice * $deckCard->quantity;
                        $formatted = number_format($totalConverted, 2);
                    @endphp
                    <p class="text-green-400 text-xs font-semibold mt-2">
                        @if(in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF']))
                            {{ $symbol }}{{ $formatted }}
                        @else
                            {{ $formatted }} {{ $symbol }}
                        @endif
                    </p>
                    <p class="text-gray-500 text-xs">
                        (€{{ number_format($deckCard->cached_price * $deckCard->quantity, 2) }})
                    </p>
                @else
                    <p class="text-green-400 text-xs font-semibold mt-2">
                        €{{ number_format($deckCard->cached_price * $deckCard->quantity, 2) }}
                    </p>
                @endif
            @else
                <p class="text-gray-500 text-xs mt-2">{{ __('decks/show.price_unavailable') }}</p>
            @endif
            @endcan
            
            <!-- Photo Upload Section (Premium only) -->
            @can('uploadCardPhotos')
            <div class="mt-2 border-t border-white/10 pt-2">
                @if($deckCard->photos->count() > 0)
                    <!-- Show photos count and link -->
                    <button onclick="openDeckPhotoModal({{ $deckCard->id }})" class="flex items-center gap-2 mb-1 w-full text-left hover:bg-white/5 px-2 py-1 rounded transition">
                        <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-xs text-blue-400 underline">{{ $deckCard->photos->count() }} {{ $deckCard->photos->count() === 1 ? 'photo' : 'photos' }}</span>
                    </button>
                @endif
                <form method="POST" action="{{ route('decks.cards.photos.upload', $deckCard) }}" enctype="multipart/form-data" class="relative">
                    @csrf
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" id="deck-photo-{{ $deckCard->id }}" onchange="showDeckUploadLoader(this.form)">
                    <label for="deck-photo-{{ $deckCard->id }}" class="w-full text-xs px-2 py-1 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded transition cursor-pointer flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('photos.upload.button') }}
                    </label>
                </form>
            </div>
            @endcan
            
            <!-- Actions -->
            <div class="flex gap-2 mt-3">
                <!-- Update Quantity -->
                <form method="POST" action="{{ route('decks.cards.updateQuantity', [$deck, $deckCard]) }}" class="flex-1 flex items-center gap-1">
                    @csrf
                    @method('PATCH')
                    <input 
                        type="number" 
                        name="quantity" 
                        value="{{ $deckCard->quantity }}" 
                        min="1" 
                        max="4"
                        class="w-12 px-2 py-1 bg-black/50 border border-white/20 rounded text-white text-center text-xs"
                        onchange="this.form.submit()"
                    >
                    <button type="submit" class="hidden">Update</button>
                </form>
                
                <!-- Remove Button -->
                <form method="POST" action="{{ route('decks.cards.remove', [$deck, $deckCard]) }}" onsubmit="return confirm('{{ __('decks/show.remove_confirm') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1 bg-red-600/20 hover:bg-red-600/40 border border-red-500/30 text-red-400 rounded text-xs transition">
                        {{ __('decks/show.remove') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
