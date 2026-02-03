@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-white mb-2">{{ __('tcg/interactions.wishlist_title') }}</h1>
            <p class="text-gray-400">{{ __('tcg/interactions.wishlist_subtitle', ['count' => $wishlistProducts->total()]) }}</p>
        </div>

        @if($wishlistProducts->isEmpty())
            <!-- Empty State -->
            <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('tcg/interactions.no_wishlist') }}</h3>
                <p class="text-gray-400 mb-6">{{ __('tcg/interactions.no_wishlist_description') }}</p>
                @php
                    $browseUrl = match($catalogBackend) {
                        'tcgdex' => route('pokemon.sets'),
                        'cmapi' => route('cmapi.sets.index', ['game' => $currentGame->slug ?? 'lorcana']),
                        default => route('tcg.expansions.index')
                    };
                @endphp
                <a href="{{ $browseUrl }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    {{ __('tcg/interactions.browse_cards') }}
                </a>
            </div>
        @else
            <!-- Cards Grid -->
            <div class="grid gap-3 mb-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                @foreach($wishlistProducts as $item)
                    @php
                        // Determine backend and get card data
                        if ($catalogBackend === 'tcgdex' && $item->tcgdexCard) {
                            $card = $item->tcgdexCard;
                            $cardName = is_array($card->name) ? ($card->name['en'] ?? $card->tcgdex_id) : $card->name;
                            $imageUrl = $card->image_small_url . '/high.webp';
                            $cardUrl = route('pokemon.card', $card->tcgdex_id);
                            $cardNumber = $card->national_pokedex_number ?? $card->card_number ?? '';
                            $rawSetName = $card->set->name ?? '';
                            $setName = is_array($rawSetName) ? ($rawSetName[app()->getLocale()] ?? $rawSetName['en'] ?? '') : $rawSetName;
                            $removeRoute = route('pokemon.cards.wishlist', $card->tcgdex_id);
                        } elseif ($catalogBackend === 'cmapi' && $item->cmapiCard) {
                            $card = $item->cmapiCard;
                            $cardName = $card->name;
                            $imageUrl = $card->image_small_url;
                            $cardUrl = route('cmapi.cards.show', [$currentGame->slug ?? 'lorcana', $card->cmapi_id]);
                            $cardNumber = $card->card_number ?? '';
                            $setName = $card->episode_name ?? '';
                            $removeRoute = route('cmapi.cards.wishlist', [$currentGame->slug ?? 'lorcana', $card->cmapi_id]);
                        } else {
                            // TCGCSV
                            $card = $item->product ?? $item;
                            $cardName = $card->name;
                            $imageUrl = $card->rapidapiCard->image_url ?? $card->image_url ?? 'https://via.placeholder.com/245x342/1a1a19/666?text=No+Image';
                            $cardUrl = route('tcg.cards.show', $card->product_id);
                            $cardNumber = $card->card_number ?? '';
                            $setName = $card->group->abbreviation ?? '';
                            $removeRoute = route('tcg.items.wishlist', $card->product_id);
                        }
                    @endphp
                    
                    <div class="bg-[#1a1a19] border border-white/10 rounded-lg hover:border-white/30 hover:shadow-xl transition overflow-hidden group relative">
                        
                        <!-- Remove Button -->
                        <div class="absolute top-2 right-2 z-10">
                            <form action="{{ $removeRoute }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 bg-yellow-500/90 hover:bg-yellow-600 rounded-full text-white transition" title="{{ __('tcg/interactions.remove_from_wishlist') }}">
                                    <svg class="w-4 h-4" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        
                        <a href="{{ $cardUrl }}">
                            <div class="aspect-[245/342] bg-black/50 overflow-hidden">
                                <img 
                                    src="{{ $imageUrl }}" 
                                    alt="{{ $cardName }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    loading="lazy"
                                >
                            </div>
                            <div class="p-2">
                                <h3 class="text-xs font-semibold text-white truncate group-hover:text-blue-400 transition">
                                    {{ $cardName }}
                                </h3>
                                <div class="flex items-center justify-between mt-0.5">
                                    @if($cardNumber)
                                        <p class="text-xs text-gray-400">#{{ $cardNumber }}</p>
                                    @else
                                        <span></span>
                                    @endif
                                    @if($setName)
                                        <span class="text-xs text-gray-500">{{ $setName }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $wishlistProducts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
