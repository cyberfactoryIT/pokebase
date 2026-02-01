@extends('layouts.app')

@section('content')
<div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Link -->
            <div class="mb-4">
                <a href="{{ route('pokemon.sets') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← {{ __('catalog.back_to_sets') }}
                </a>
            </div>

            <!-- Set Info -->
            <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden mb-6">
                <div class="p-6 text-gray-100">
                    <div class="flex items-start gap-6">
                        @if($set->logo_url)
                            <img src="{{ $set->logo_url }}.webp" 
                                 alt="{{ $set->name_en }}"
                                 class="w-48 h-32 object-contain">
                        @endif
                        <div>
                            <h3 class="text-2xl font-bold mb-2 text-white">{{ $set->name_en }}</h3>
                            <p class="text-gray-400">{{ __('catalog.series') }}: {{ $set->series_name['en'] ?? 'N/A' }}</p>
                            <p class="text-gray-400">{{ __('catalog.total_cards') }}: {{ $set->card_count_total ?? 0 }}</p>
                            @if($set->released_at)
                                <p class="text-gray-400">{{ __('catalog.released') }}: {{ $set->released_at->format('F j, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6 text-gray-100">
                    @if($cards->isEmpty())
                        <p class="text-gray-500">{{ __('catalog.no_cards_found') }}</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($cards as $card)
                                @php
                                    $isLiked = $userInteractions && in_array($card->id, $userInteractions['liked']);
                                    $isWishlisted = $userInteractions && in_array($card->id, $userInteractions['wishlist']);
                                    $isWatched = $userInteractions && in_array($card->id, $userInteractions['watched']);
                                @endphp
                                
                                <div class="relative bg-[#1a1a19] border border-white/20 rounded-lg p-3 hover:shadow-xl hover:border-white/40 transition-all group">
                                    <a href="{{ route('pokemon.card', $card->tcgdex_id) }}" class="block">
                                        @if($card->image_small_url)
                                            <img src="{{ $card->image_small_url }}/high.webp" 
                                                 alt="{{ $card->name_en ?? $card->tcgdex_id }}"
                                                 class="w-full rounded mb-2"
                                                 loading="lazy">
                                        @endif
                                        
                                        <div class="text-sm">
                                            <div class="flex items-center justify-between mb-1">
                                                <p class="font-semibold truncate text-white flex-1">{{ $card->name_en ?? $card->tcgdex_id }}</p>
                                                
                                                @auth
                                                <!-- Interaction buttons - a destra del nome -->
                                                <div class="flex gap-1 ml-2">
                                                    <!-- Like -->
                                                    <button 
                                                        onclick="event.preventDefault(); event.stopPropagation(); toggleLike('{{ $card->tcgdex_id }}', this)" 
                                                        class="p-1 {{ $isLiked ? 'bg-red-600' : 'bg-gray-700/90' }} hover:bg-red-500 rounded text-white transition" 
                                                        title="{{ __('catalog.like') }}"
                                                        data-card-id="{{ $card->tcgdex_id }}">
                                                        <svg class="w-3 h-3" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                                        </svg>
                                                    </button>
                                                    <!-- Wishlist -->
                                                    <button 
                                                        onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist('{{ $card->tcgdex_id }}', this)" 
                                                        class="p-1 {{ $isWishlisted ? 'bg-purple-600' : 'bg-gray-700/90' }} hover:bg-purple-500 rounded text-white transition" 
                                                        title="{{ __('catalog.wishlist') }}"
                                                        data-card-id="{{ $card->tcgdex_id }}">
                                                        <svg class="w-3 h-3" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                        </svg>
                                                    </button>
                                                    <!-- Watch -->
                                                    <button 
                                                        onclick="event.preventDefault(); event.stopPropagation(); toggleWatch('{{ $card->tcgdex_id }}', this)" 
                                                        class="p-1 {{ $isWatched ? 'bg-yellow-600' : 'bg-gray-700/90' }} hover:bg-yellow-500 rounded text-white transition" 
                                                        title="{{ __('catalog.watch') }}"
                                                        data-card-id="{{ $card->tcgdex_id }}">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                @endauth
                                            </div>
                                            
                                            <p class="text-gray-400 text-xs">{{ $card->number ?? $card->local_id }}
                                                @if($card->rarity)
                                                 {{ $card->rarity }} 
                                                @endif
                                                @if($card->price_eur)
                                                    @auth
                                                        @if(auth()->user()->isAdvanced() || auth()->user()->isPremium())
                                                            @php
                                                                $user = auth()->user();
                                                                $preferredCurrency = $user->preferred_currency ?? 'EUR';
                                                                $needsConversion = $preferredCurrency && $preferredCurrency !== 'EUR';
                                                                
                                                                if ($needsConversion) {
                                                                    $convertedPrice = \App\Services\CurrencyService::convert($card->price_eur, 'EUR', $preferredCurrency);
                                                                    $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                                    $symbolAfter = in_array($preferredCurrency, ['DKK', 'SEK', 'NOK']);
                                                                    $formatted = $symbolAfter ? number_format($convertedPrice, 2) . ' ' . $symbol : $symbol . number_format($convertedPrice, 2);
                                                                } else {
                                                                    $formatted = '€' . number_format($card->price_eur, 2);
                                                                }
                                                            @endphp
                                                            <span class="text-sm font-semibold text-green-400 mt-2">
                                                                {{ $formatted }}
                                                                @if($needsConversion)
                                                                    <span class="text-xs text-gray-500">(€{{ number_format($card->price_eur, 2) }})</span>
                                                                @endif
                                                            </span>
                                                        @endif
                                                    @endauth
                                                @endif
                                            </p>
                                            
                                            
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            {{ $cards->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
// Interaction functions for TCGDEX cards
async function toggleLike(cardId, button) {
    try {
        const response = await fetch(`/pokemon/cards/${cardId}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }
        
        const data = await response.json();
        
        if (data.status) {
            const svg = button.querySelector('svg');
            if (data.status === 'liked') {
                button.classList.remove('bg-gray-700/90');
                button.classList.add('bg-red-600');
                svg.setAttribute('fill', 'currentColor');
            } else {
                button.classList.remove('bg-red-600');
                button.classList.add('bg-gray-700/90');
                svg.setAttribute('fill', 'none');
            }
        }
    } catch (error) {
        console.error('Error toggling like:', error);
    }
}

async function toggleWishlist(cardId, button) {
    try {
        const response = await fetch(`/pokemon/cards/${cardId}/wishlist`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }
        
        const data = await response.json();
        
        if (data.status) {
            const svg = button.querySelector('svg');
            if (data.status === 'added') {
                button.classList.remove('bg-gray-700/90');
                button.classList.add('bg-purple-600');
                svg.setAttribute('fill', 'currentColor');
            } else {
                button.classList.remove('bg-purple-600');
                button.classList.add('bg-gray-700/90');
                svg.setAttribute('fill', 'none');
            }
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
    }
}

async function toggleWatch(cardId, button) {
    try {
        const response = await fetch(`/pokemon/cards/${cardId}/watch`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }
        
        const data = await response.json();
        
        if (data.status) {
            if (data.status === 'watched') {
                button.classList.remove('bg-gray-700/90');
                button.classList.add('bg-yellow-600');
            } else {
                button.classList.remove('bg-yellow-600');
                button.classList.add('bg-gray-700/90');
            }
        }
    } catch (error) {
        console.error('Error toggling watch:', error);
    }
}
</script>

@endsection
