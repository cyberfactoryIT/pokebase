@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('decks.index') }}" class="text-gray-400 hover:text-white transition flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('decks/show.back_to_decks') }}
            </a>

            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $deck->name }}</h1>
                    <div class="flex items-center gap-4 text-gray-400">
                        @if($deck->format)
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded">{{ $deck->format }}</span>
                        @endif
                        <span>{{ $deck->totalCards() }} cards</span>
                        <span>Created {{ $deck->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Card Limit Badge (Free users only) -->
                    @if(auth()->user()->isFree())
                        @php
                            $cardLimit = auth()->user()->cardLimit();
                            $currentUsage = auth()->user()->currentCardUsage();
                            $percentUsed = $cardLimit > 0 ? round(($currentUsage / $cardLimit) * 100) : 0;
                            $isNearLimit = $percentUsed >= 80;
                            $isAtLimit = $currentUsage >= $cardLimit;
                        @endphp
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 {{ $isAtLimit ? 'bg-red-500/20 text-red-300' : ($isNearLimit ? 'bg-yellow-500/20 text-yellow-300' : 'bg-blue-500/20 text-blue-300') }} rounded-lg text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="font-semibold">{{ $currentUsage }}/{{ $cardLimit }}</span>
                        </div>
                    @endif
                    
                    <a href="{{ route('decks.edit', $deck) }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-gray-300 rounded-lg transition">
                        {{ __('decks/show.edit_deck') }}
                    </a>
                </div>
            </div>

            @if($deck->description)
            <p class="text-gray-400 mt-4">{{ $deck->description }}</p>
            @endif
        </div>

        @if(session('success'))
        <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4 mb-6">
            <p class="text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-500/20 border border-red-400/30 text-red-300 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-semibold">{{ session('error') }}</p>
                    @if(session('error_detail'))
                        <p class="text-sm mt-1">{{ session('error_detail') }}</p>
                        <a href="{{ route('profile.subscription') }}" class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            {{ __('limits.cards.cta_upgrade') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Deck Statistics -->
        @if(!$deck->deckCards->isEmpty())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Cards -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Cards</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_cards'] }}</p>
                    </div>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Unique Cards -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Unique Cards</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['unique_cards'] }}</p>
                    </div>
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>

            @can('seePrices')
            <!-- Deck Value -->
            @php
                $user = auth()->user();
                $preferredCurrency = $user->preferred_currency;
                $defaultCurrency = $preferredCurrency ?: 'EUR';
                
                // If user has a preferred currency, convert the prices
                if ($preferredCurrency) {
                    $displayValueEur = \App\Services\CurrencyService::convert($topStats['total_value_eur'], 'EUR', $preferredCurrency);
                    $displayValueUsd = \App\Services\CurrencyService::convert($topStats['total_value_usd'], 'USD', $preferredCurrency);
                } else {
                    $displayValueEur = $topStats['total_value_eur'];
                    $displayValueUsd = $topStats['total_value_usd'];
                }
            @endphp
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6" x-data="{ 
                currency: localStorage.getItem('deckCurrency') || '{{ $defaultCurrency }}',
                preferredCurrency: '{{ $preferredCurrency }}',
                setCurrency(curr) {
                    this.currency = curr;
                    localStorage.setItem('deckCurrency', curr);
                }
            }">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1">
                        <p class="text-gray-400 text-sm mb-2">Estimated Value</p>
                        
                        @if($preferredCurrency)
                            <!-- User has preferred currency - show converted price with original -->
                            <div x-show="currency === 'EUR'">
                                @if($topStats['total_value_eur'] > 0)
                                    <p class="text-3xl font-bold text-white">
                                        @php
                                            $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                            $formatted = number_format($displayValueEur, 2);
                                            if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                echo "{$symbol}{$formatted}";
                                            } else {
                                                echo "{$formatted} {$symbol}";
                                            }
                                        @endphp
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('collection/index.original_price') }}: €{{ number_format($topStats['total_value_eur'], 2) }}</p>
                                    <p class="text-xs text-gray-500">{{ $topStats['cards_with_prices_eur'] }} cards priced</p>
                                @else
                                    <p class="text-xl text-gray-500">No EUR prices</p>
                                @endif
                            </div>
                            <div x-show="currency === 'USD'">
                                @if($topStats['total_value_usd'] > 0)
                                    <p class="text-3xl font-bold text-white">
                                        @php
                                            $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                            $formatted = number_format($displayValueUsd, 2);
                                            if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                echo "{$symbol}{$formatted}";
                                            } else {
                                                echo "{$formatted} {$symbol}";
                                            }
                                        @endphp
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('collection/index.original_price') }}: ${{ number_format($topStats['total_value_usd'], 2) }}</p>
                                    <p class="text-xs text-gray-500">{{ $topStats['cards_with_prices_usd'] }} cards priced</p>
                                @else
                                    <p class="text-xl text-gray-500">No USD prices</p>
                                @endif
                            </div>
                        @else
                            <!-- No preferred currency - show default EUR/USD -->
                            <div x-show="currency === 'EUR'">
                                @if($topStats['total_value_eur'] > 0)
                                    <p class="text-3xl font-bold text-white">€{{ number_format($topStats['total_value_eur'], 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $topStats['cards_with_prices_eur'] }} cards priced</p>
                                @else
                                    <p class="text-xl text-gray-500">No EUR prices</p>
                                @endif
                            </div>
                            <div x-show="currency === 'USD'">
                                @if($topStats['total_value_usd'] > 0)
                                    <p class="text-3xl font-bold text-white">${{ number_format($topStats['total_value_usd'], 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $topStats['cards_with_prices_usd'] }} cards priced</p>
                                @else
                                    <p class="text-xl text-gray-500">No USD prices</p>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <div class="bg-green-500/20 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <!-- Currency Toggle -->
                        <div class="inline-flex bg-black/50 border border-white/15 rounded-lg p-1">
                            <button 
                                @click="setCurrency('EUR')"
                                :class="currency === 'EUR' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition"
                            >
                                EUR
                            </button>
                            <button 
                                @click="setCurrency('USD')"
                                :class="currency === 'USD' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition"
                            >
                                USD
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Prices Hidden for Free Users -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center justify-center flex-col py-8">
                    <svg class="w-12 h-12 text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">{{ __('prices.hidden.title') }}</h3>
                    <p class="text-gray-400 text-center mb-6">{{ __('prices.hidden.body') }}</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('billing.index') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            {{ __('prices.hidden.cta_upgrade') }}
                        </a>
                        <span class="text-gray-400 flex items-center">{{ __('prices.hidden.or') }}</span>
                        <a href="{{ route('deck-evaluation.packages.index') }}" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition">
                            {{ __('prices.hidden.cta_deck_evaluation') }}
                        </a>
                    </div>
                </div>
            </div>
            @endcan
        </div>

        <!-- Rarity & Set Distribution -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Rarity Distribution -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Rarity Distribution</h3>
                <div class="space-y-3">
                    @foreach($topStats['rarity_distribution'] as $rarity => $data)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300">{{ $rarity }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-white font-semibold">{{ $data['total_quantity'] }}</span>
                                <span class="text-gray-500 text-sm">({{ $data['count'] }} unique)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Set Distribution -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Top Sets</h3>
                <div class="space-y-3">
                    @foreach($topStats['set_distribution'] as $set)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300 truncate flex-1 mr-2">{{ $set['set_name'] }}</span>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-white font-semibold">{{ $set['total_quantity'] }}</span>
                                <span class="text-gray-500 text-sm">({{ $set['count'] }} unique)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Add Cards - Two Columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Add from Collection -->
            <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">{{ __('decks/show.add_from_collection') }}</h2>
                <div class="relative" x-data="{ searchOpen: false }" @click.away="searchOpen = false">
                    <input 
                        type="text" 
                        id="deck-card-search" 
                        placeholder="{{ __('decks/show.search_collection_placeholder') }}"
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @focus="searchOpen = true"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div id="deck-search-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                        <!-- Results will be inserted here by JS -->
                    </div>
                </div>
            </div>

            <!-- Add from Catalog -->
            <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">{{ __('decks/show.add_from_catalog') }}</h2>
                <div class="relative" x-data="{ catalogSearchOpen: false }" @click.away="catalogSearchOpen = false">
                    <input 
                        type="text" 
                        id="catalog-card-search" 
                        placeholder="{{ __('decks/show.search_catalog_placeholder') }}"
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        @focus="catalogSearchOpen = true"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div id="catalog-search-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                        <!-- Results will be inserted here by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Deck Contents -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            @if($deck->deckCards->isEmpty())
            <!-- Empty Deck -->
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-white text-xl font-semibold mb-2">{{ __('decks/show.empty_state_title') }}</h3>
                <p class="text-gray-400 mb-6">{{ __('decks/show.empty_state_text') }}</p>
                <a href="{{ route('tcg.expansions.index') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    {{ __('decks/show.browse_cards') }}
                </a>
            </div>
            @else
            <!-- Card Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($deck->deckCards as $deckCard)
                @php
                    $card = $deckCard->card;
                    $inCollection = $card ? auth()->user()->collection()->where('product_id', $card->product_id)->exists() : false;
                    $displayImage = $card->hd_image_url ?? $card->image_url;
                @endphp
                @if($card)
                <div class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition overflow-hidden group relative">
                    <!-- Quantity Badge -->
                    <div class="absolute top-2 left-2 z-10 bg-blue-600/90 text-white px-2 py-1 rounded text-sm font-semibold">
                        x{{ $deckCard->quantity }}
                    </div>
                    
                    <!-- Not in Collection Badge -->
                    @if(!$inCollection)
                    <div class="absolute top-2 right-2 z-10">
                        <form method="POST" action="{{ route('collection.add') }}" class="inline" onsubmit="event.preventDefault(); quickAddCardToCollection({{ $card->product_id }}, '{{ addslashes($card->name) }}', this);">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $card->product_id }}">
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
                    <div class="aspect-[245/342] bg-black/50 overflow-hidden cursor-pointer" onclick="window.location.href='/tcg/cards/{{ $card->product_id }}'">
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
                        <h4 class="text-white font-semibold text-sm truncate group-hover:text-blue-400 transition cursor-pointer" onclick="window.location.href='/tcg/cards/{{ $card->product_id }}'">
                            {{ $card->name }}
                        </h4>
                        <p class="text-gray-400 text-xs truncate mt-1">
                            {{ $card->group->name ?? 'Unknown Set' }}
                            @if($card->card_number)
                            · #{{ $card->card_number }}
                            @endif
                        </p>
                        
                        @can('seePrices')
                        <!-- Price Display with Currency Toggle -->
                        @php
                            $tcgPrice = $card->prices->first();
                            $marketPriceUsd = $tcgPrice?->market_price ?? 0;
                            
                            // EUR price from RapidAPI Cardmarket data
                            $marketPriceEur = 0;
                            $rapidapiCard = $card->rapidapiCard;
                            if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                                $marketPriceEur = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                            }
                            
                            // Convert to preferred currency if set
                            if ($preferredCurrency) {
                                $convertedPriceEur = $marketPriceEur > 0 ? \App\Services\CurrencyService::convert($marketPriceEur, 'EUR', $preferredCurrency) : 0;
                                $convertedPriceUsd = $marketPriceUsd > 0 ? \App\Services\CurrencyService::convert($marketPriceUsd, 'USD', $preferredCurrency) : 0;
                            }
                        @endphp
                        
                        <div class="mt-2" x-data="{ currency: localStorage.getItem('deckCurrency') || '{{ $defaultCurrency }}' }">
                            <!-- EUR Price -->
                            <div x-show="currency === 'EUR'">
                                @if($marketPriceEur > 0)
                                    @if($preferredCurrency)
                                        <p class="text-green-400 text-xs font-semibold">
                                            @php
                                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                $totalConverted = $convertedPriceEur * $deckCard->quantity;
                                                $formatted = number_format($totalConverted, 2);
                                                if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                    echo "{$symbol}{$formatted}";
                                                } else {
                                                    echo "{$formatted} {$symbol}";
                                                }
                                            @endphp
                                        </p>
                                        <p class="text-gray-500 text-xs">(€{{ number_format($marketPriceEur * $deckCard->quantity, 2) }})</p>
                                    @else
                                        <p class="text-green-400 text-xs font-semibold">€{{ number_format($marketPriceEur * $deckCard->quantity, 2) }}</p>
                                    @endif
                                @else
                                    <p class="text-gray-500 text-xs">No EUR price</p>
                                @endif
                            </div>
                            
                            <!-- USD Price -->
                            <div x-show="currency === 'USD'">
                                @if($marketPriceUsd > 0)
                                    @if($preferredCurrency)
                                        <p class="text-green-400 text-xs font-semibold">
                                            @php
                                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                $totalConverted = $convertedPriceUsd * $deckCard->quantity;
                                                $formatted = number_format($totalConverted, 2);
                                                if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                    echo "{$symbol}{$formatted}";
                                                } else {
                                                    echo "{$formatted} {$symbol}";
                                                }
                                            @endphp
                                        </p>
                                        <p class="text-gray-500 text-xs">(${{ number_format($marketPriceUsd * $deckCard->quantity, 2) }})</p>
                                    @else
                                        <p class="text-green-400 text-xs font-semibold">${{ number_format($marketPriceUsd * $deckCard->quantity, 2) }}</p>
                                    @endif
                                @else
                                    <p class="text-gray-500 text-xs">No USD price</p>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="mt-2">
                            <p class="text-gray-500 text-xs">🔒 {{ __('prices.hidden.title') }}</p>
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
                                <button type="submit" class="p-1.5 bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Deck card search from collection
const deckSearchInput = document.getElementById('deck-card-search');
const deckSearchDropdown = document.getElementById('deck-search-dropdown');
const catalogSearchInput = document.getElementById('catalog-card-search');
const catalogSearchDropdown = document.getElementById('catalog-search-dropdown');
const deckId = {{ $deck->id }};
let deckSearchDebounceTimer = null;
let catalogSearchDebounceTimer = null;
let currentDeckSearchRequest = 0;
let currentCatalogSearchRequest = 0;
let userCollectionProductIds = new Set();

// Load user collection IDs for checking
async function loadUserCollectionIds() {
    try {
        const response = await fetch('/collection/ids', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        userCollectionProductIds = new Set(data);
        console.log(`Loaded ${data.length} collection IDs`);
    } catch (error) {
        console.error('Error loading collection:', error);
    }
}

loadUserCollectionIds();

deckSearchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    
    clearTimeout(deckSearchDebounceTimer);
    
    if (query.length < 2) {
        deckSearchDropdown.classList.add('hidden');
        deckSearchDropdown.innerHTML = '';
        return;
    }
    
    deckSearchDebounceTimer = setTimeout(() => {
        searchCollectionCards(query);
    }, 300);
});

catalogSearchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    
    clearTimeout(catalogSearchDebounceTimer);
    
    if (query.length < 2) {
        catalogSearchDropdown.classList.add('hidden');
        catalogSearchDropdown.innerHTML = '';
        return;
    }
    
    catalogSearchDebounceTimer = setTimeout(() => {
        searchCatalogCards(query);
    }, 300);
});

async function searchCollectionCards(query) {
    const requestId = ++currentDeckSearchRequest;
    
    try {
        // Search only in user's collection with limit
        const response = await fetch(`/api/search/cards?q=${encodeURIComponent(query)}&collection_only=1&limit=20`);
        
        if (requestId !== currentDeckSearchRequest) return;
        
        const data = await response.json();
        
        if (data.length === 0) {
            deckSearchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">No cards found in your collection</div>';
            deckSearchDropdown.classList.remove('hidden');
            return;
        }
        
        const resultsHTML = data.map(card => `
            <div class="px-4 py-3 hover:bg-white/10 cursor-pointer border-b border-white/10 last:border-b-0 flex items-center gap-3"
                 onclick="addCardToDeck(${card.product_id}, '${escapeHtml(card.name)}')">
                <div class="flex-shrink-0 w-12 h-16 bg-black/50 rounded overflow-hidden">
                    ${card.image_url ? `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-cover">` : ''}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white font-medium truncate">${escapeHtml(card.name)}</div>
                    <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) : ''}</div>
                </div>
                <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
        `).join('');
        
        deckSearchDropdown.innerHTML = resultsHTML;
        deckSearchDropdown.classList.remove('hidden');
    } catch (error) {
        console.error('Search error:', error);
    }
}

async function searchCatalogCards(query) {
    const requestId = ++currentCatalogSearchRequest;
    
    try {
        // Search all cards (no collection filter)
        const response = await fetch(`/api/search/cards?q=${encodeURIComponent(query)}`);
        
        if (requestId !== currentCatalogSearchRequest) return;
        
        const data = await response.json();
        
        if (data.length === 0) {
            catalogSearchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">No cards found</div>';
            catalogSearchDropdown.classList.remove('hidden');
            return;
        }
        
        const resultsHTML = data.map(card => {
            const inCollection = userCollectionProductIds.has(card.product_id);
            return `
                <div class="px-4 py-3 hover:bg-white/10 border-b border-white/10 last:border-b-0 flex items-center gap-3">
                    <div class="flex-shrink-0 w-12 h-16 bg-black/50 rounded overflow-hidden">
                        ${card.image_url ? `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-cover">` : ''}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <div class="text-white font-medium truncate">${escapeHtml(card.name)}</div>
                            ${!inCollection ? '<span class="text-orange-400 text-xs font-semibold whitespace-nowrap">(Not in Collection)</span>' : ''}
                        </div>
                        <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) : ''}</div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        ${!inCollection ? `
                            <button onclick="event.stopPropagation(); quickAddToCollection(${card.product_id}, '${escapeHtml(card.name)}')" 
                                    class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition flex items-center gap-1"
                                    title="Add to Collection">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Collection
                            </button>
                        ` : ''}
                        <button onclick="addCardToDeck(${card.product_id}, '${escapeHtml(card.name)}')"
                                class="px-2 py-1 ${inCollection ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-600 hover:bg-orange-700'} text-white text-xs rounded transition flex items-center gap-1"
                                title="Add to Deck">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Deck
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        
        catalogSearchDropdown.innerHTML = resultsHTML;
        catalogSearchDropdown.classList.remove('hidden');
    } catch (error) {
        console.error('Search error:', error);
    }
}

async function quickAddToCollection(productId, cardName) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        
        const response = await fetch('{{ route("collection.add") }}', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            // Add to local collection set
            userCollectionProductIds.add(productId);
            // Refresh both dropdowns to update UI
            catalogSearchInput.dispatchEvent(new Event('input'));
            alert(`${cardName} added to collection!`);
        } else {
            alert('Failed to add card to collection');
        }
    } catch (error) {
        console.error('Error adding to collection:', error);
        alert('Failed to add card to collection');
    }
}

async function addCardToDeck(productId, cardName) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        
        const response = await fetch(`/decks/${deckId}/cards`, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            // Reload page to show updated deck
            window.location.reload();
        } else {
            alert('Failed to add card to deck');
        }
    } catch (error) {
        console.error('Error adding card:', error);
        alert('Failed to add card to deck');
    }
}

// Quick add to collection from deck list
async function quickAddCardToCollection(productId, cardName, form) {
    try {
        const response = await fetch('{{ route("collection.add") }}', {
            method: 'POST',
            body: new FormData(form)
        });
        
        if (response.ok) {
            userCollectionProductIds.add(productId);
            location.reload(); // Reload to update the badge
        } else {
            alert('Failed to add card to collection');
        }
    } catch (error) {
        console.error('Error adding to collection:', error);
        alert('Failed to add card to collection');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
