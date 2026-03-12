<!-- Recent Additions Section - CMAPI (Lorcana/One Piece) -->
@php
    $recentAdditions = \App\Models\UserCollection::where('user_id', Auth::id())
        ->whereNotNull('cmapi_card_id')
        ->whereHas('cmapiCard', function ($q) use ($currentGame) {
            $q->where('game', $currentGame->slug);
        })
        ->with('cmapiCard.set')
        ->latest()
        ->take(6)
        ->get();
@endphp

@if($recentAdditions && $recentAdditions->isNotEmpty())
<div class="bg-white/5 border border-white/10 rounded-xl p-6">
    <h3 class="font-semibold text-xl text-white mb-6 flex items-center gap-3">
        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        {{ __('dashboard.recent_additions') }}
    </h3>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($recentAdditions as $item)
        @php
            $card = $item->cmapiCard;
            if (!$card) continue;
            $cardName = $card->name;
            $cardImage = $card->image_large_url ?? $card->image_small_url;
        @endphp
        <a href="/{{ $currentGame->slug }}/cards/{{ $card->cmapi_id }}" 
           class="group relative bg-white/5 hover:bg-white/10 border border-white/10 hover:border-green-500/50 rounded-lg overflow-hidden transition-all">
            
            <!-- Card Image -->
            <div class="aspect-[2/3] bg-gradient-to-br from-gray-800 to-gray-900">
                @if($cardImage)
                    <img src="{{ $cardImage }}" 
                         alt="{{ $cardName }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- Card Info Overlay -->
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/70 to-transparent p-3">
                <p class="text-white text-sm font-medium truncate mb-1">{{ $cardName }}</p>
                
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-300">{{ $item->quantity }}x</span>
                    @if($item->cached_price)
                        <span class="text-green-400 font-semibold">€{{ number_format($item->cached_price, 2) }}</span>
                    @elseif($card->price_eur)
                        <span class="text-green-400 font-semibold">€{{ number_format($card->price_eur, 2) }}</span>
                    @endif
                </div>
            </div>
            
            <!-- New Badge -->
            <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                {{ __('dashboard.new') }}
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
