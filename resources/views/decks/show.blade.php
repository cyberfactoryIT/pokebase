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

                    <!-- Share Deck Button -->
                    @if($deck->is_shared)
                        <!-- Deck is currently shared -->
                        <div x-data="{ copied: false }" class="flex items-center gap-2">
                            <button 
                                @click="navigator.clipboard.writeText('{{ $deck->public_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span x-show="!copied">{{ __('sharing.deck.copy_link') }}</span>
                                <span x-show="copied" x-cloak>{{ __('sharing.deck.copied') }}</span>
                            </button>
                            <form action="{{ route('decks.unshare', $deck) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600/80 hover:bg-red-700 text-white rounded-lg transition">
                                    {{ __('sharing.deck.unshare') }}
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Deck is not shared -->
                        @can('shareDeck', $deck)
                            <form action="{{ route('decks.share', $deck) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    {{ __('sharing.deck.share') }}
                                </button>
                            </form>
                        @else
                            <!-- Show why user cannot share -->
                            @if(auth()->user()->maxSharedDecks() === 0)
                                <div class="relative group">
                                    <button disabled class="px-4 py-2 bg-gray-600 text-gray-400 rounded-lg cursor-not-allowed flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        {{ __('sharing.deck.share') }}
                                    </button>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-gray-800 text-white text-sm rounded-lg p-3 opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                                        <p class="font-semibold mb-1">{{ __('sharing.limit.free.title') }}</p>
                                        <p class="text-gray-300 mb-2">{{ __('sharing.limit.free.body') }}</p>
                                        <a href="{{ route('profile.subscription') }}" class="text-blue-400 hover:text-blue-300">{{ __('sharing.limit.free.cta_upgrade') }} →</a>
                                    </div>
                                </div>
                            @else
                                <div class="relative group">
                                    <button disabled class="px-4 py-2 bg-gray-600 text-gray-400 rounded-lg cursor-not-allowed flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        {{ __('sharing.deck.share') }}
                                    </button>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-gray-800 text-white text-sm rounded-lg p-3 opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                                        <p class="font-semibold mb-1">{{ __('sharing.limit.reached.title') }}</p>
                                        <p class="text-gray-300 mb-2">{{ __('sharing.limit.reached.body', ['limit' => auth()->user()->maxSharedDecks(), 'current' => auth()->user()->sharedDecksCount()]) }}</p>
                                        <a href="{{ route('profile.subscription') }}" class="text-blue-400 hover:text-blue-300">{{ __('sharing.limit.reached.cta_upgrade') }} →</a>
                                    </div>
                                </div>
                            @endif
                        @endcan
                    @endif
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
                        <p class="text-3xl font-bold text-white mt-1" data-stat="total-cards">{{ $stats['total_cards'] }}</p>
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
                        <p class="text-3xl font-bold text-white mt-1" data-stat="unique-cards">{{ $stats['unique_cards'] }}</p>
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
                                    <p class="text-3xl font-bold text-white" data-stat="estimated-value-eur">
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
                                    <p class="text-xs text-gray-500 mt-1">{{ __('collection/index.original_price') }}: €<span data-stat="original-price-eur">{{ number_format($topStats['total_value_eur'], 2) }}</span></p>
                                    <p class="text-xs text-gray-500"><span data-stat="cards-priced-eur">{{ $topStats['cards_with_prices_eur'] }}</span> cards priced</p>
                                @else
                                    <p class="text-xl text-gray-500">No EUR prices</p>
                                @endif
                            </div>
                            <div x-show="currency === 'USD'">
                                @if($topStats['total_value_usd'] > 0)
                                    <p class="text-3xl font-bold text-white" data-stat="estimated-value-usd">
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
                                    <p class="text-xs text-gray-500 mt-1">{{ __('collection/index.original_price') }}: $<span data-stat="original-price-usd">{{ number_format($topStats['total_value_usd'], 2) }}</span></p>
                                    <p class="text-xs text-gray-500"><span data-stat="cards-priced-usd">{{ $topStats['cards_with_prices_usd'] }}</span> cards priced</p>
                                @else
                                    <p class="text-xl text-gray-500">No USD prices</p>
                                @endif
                            </div>
                        @else
                            <!-- No preferred currency - show default EUR/USD -->
                            <div x-show="currency === 'EUR'">
                                @if($topStats['total_value_eur'] > 0)
                                    <p class="text-3xl font-bold text-white" data-stat="estimated-value-eur-simple">€{{ number_format($topStats['total_value_eur'], 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-1"><span data-stat="cards-priced-eur-simple">{{ $topStats['cards_with_prices_eur'] }}</span> cards priced</p>
                                @else
                                    <p class="text-xl text-gray-500">No EUR prices</p>
                                @endif
                            </div>
                            <div x-show="currency === 'USD'">
                                @if($topStats['total_value_usd'] > 0)
                                    <p class="text-3xl font-bold text-white" data-stat="estimated-value-usd-simple">${{ number_format($topStats['total_value_usd'], 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-1"><span data-stat="cards-priced-usd-simple">{{ $topStats['cards_with_prices_usd'] }}</span> cards priced</p>
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

        @if(auth()->user()->canSeeDeckSecondRowStats())
        <!-- Rarity & Set Distribution -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Rarity Distribution -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Rarity Distribution</h3>
                    <button id="clear-rarity-filter" class="hidden px-3 py-1 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm rounded transition">
                        Clear Filter
                    </button>
                </div>
                <div class="space-y-3">
                    @foreach($topStats['rarity_distribution'] as $rarity => $data)
                        <div class="rarity-filter-item flex items-center justify-between p-2 -mx-2 rounded hover:bg-white/5 cursor-pointer transition group" 
                             data-rarity="{{ $rarity }}"
                             data-total="{{ $data['total_quantity'] }}"
                             data-unique="{{ $data['count'] }}"
                             onclick="filterByRarity('{{ $rarity }}', {{ $data['total_quantity'] }}, {{ $data['count'] }})">
                            <span class="text-gray-300 group-hover:text-white transition">{{ $rarity }}</span>
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
        @else
        <!-- Free tier - Show upsell badge for second row stats -->
        <div class="mb-6 bg-[#161615] border border-white/15 rounded-xl p-8 text-center">
            <div class="flex flex-col items-center">
                <div class="bg-blue-500/20 p-4 rounded-full mb-4">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('stats.upsell.deck_free_title') }}</h3>
                <p class="text-gray-400 mb-6 max-w-2xl">{{ __('stats.upsell.deck_free_body') }}</p>
                <a href="{{ route('billing.index') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    {{ __('stats.upsell.cta_upgrade') }}
                </a>
            </div>
        </div>
        @endif
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
            <!-- Card Grids by Backend -->
            @include('decks.partials.card-grid-tcgcsv', ['deck' => $deck, 'preferredCurrency' => $preferredCurrency, 'defaultCurrency' => $defaultCurrency])
            @include('decks.partials.card-grid-tcgdex', ['deck' => $deck])
            @include('decks.partials.card-grid-cmapi', ['deck' => $deck])
            @endif
        </div>
    </div>
</div>

@php
// Prepare photo data for JavaScript
$deckPhotoData = [];
foreach ($deck->deckCards as $deckCard) {
    if ($deckCard->photos->count() > 0) {
        $deckPhotoData[$deckCard->id] = $deckCard->photos->map(function($photo) {
            return [
                'id' => $photo->id,
                'path' => route('decks.photos.serve', $photo),
                'uploaded_at' => $photo->created_at->format('M d, Y'),
            ];
        })->toArray();
    }
}
@endphp

<!-- Photo Gallery Modal -->
<div id="deckPhotoModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/90 transition-opacity" onclick="closeDeckPhotoModal()"></div>
        <div class="relative bg-[#161615] border border-white/15 rounded-xl shadow-xl max-w-4xl w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white">Card Photos</h3>
                <button onclick="closeDeckPhotoModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="deckPhotoGalleryContent" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Photos will be loaded here -->
            </div>
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
let userCollectionTcgdexIds = new Set();

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
        userCollectionProductIds = new Set(data.product_ids || data);
        userCollectionTcgdexIds = new Set(data.tcgdex_card_ids || []);
        console.log(`Loaded ${userCollectionProductIds.size} TCGCSV + ${userCollectionTcgdexIds.size} TCGDEX collection IDs`);
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
        
        const resultsHTML = data.map(card => {
            const cardId = card.backend === 'tcgdex' ? card.tcgdex_card_id : card.product_id;
            const cardIdParam = card.backend === 'tcgdex' ? `null, ${card.tcgdex_card_id}, '${escapeHtml(card.name)}'` : `${card.product_id}, null, '${escapeHtml(card.name)}'`;
            return `
                <div class="px-4 py-3 hover:bg-white/10 cursor-pointer border-b border-white/10 last:border-b-0 flex items-center gap-3"
                     onclick="addCardToDeck(${cardIdParam})">
                    <div class="flex-shrink-0 w-12 h-16 bg-black/50 rounded overflow-hidden">
                        ${card.image_url ? `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-cover">` : ''}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-white font-medium truncate">${escapeHtml(card.name)}</div>
                        <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) + (card.set_total ? '/' + card.set_total : '') : ''}</div>
                    </div>
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            `;
        }).join('');
        
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
            const cardId = card.backend === 'tcgdex' ? card.tcgdex_card_id : card.product_id;
            const cardIdParam = card.backend === 'tcgdex' ? `null, ${card.tcgdex_card_id}, '${escapeHtml(card.name)}'` : `${card.product_id}, null, '${escapeHtml(card.name)}'`;
            const inCollection = card.backend === 'tcgdex' 
                ? userCollectionTcgdexIds.has(card.tcgdex_card_id)
                : userCollectionProductIds.has(card.product_id);
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
                        <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) + (card.set_total ? '/' + card.set_total : '') : ''}</div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        ${!inCollection ? `
                            <button onclick="event.stopPropagation(); quickAddToCollection(${cardIdParam})" 
                                    class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition flex items-center gap-1"
                                    title="Add to Collection">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Collection
                            </button>
                        ` : ''}
                        <button onclick="addCardToDeck(${cardIdParam})"
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

async function quickAddToCollection(productId, tcgdexCardId, cardName) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        if (productId) {
            formData.append('product_id', productId);
        } else if (tcgdexCardId) {
            formData.append('tcgdex_card_id', tcgdexCardId);
        }
        formData.append('quantity', 1);
        
        const response = await fetch('{{ route("collection.add") }}', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            if (productId) {
                userCollectionProductIds.add(productId);
            } else if (tcgdexCardId) {
                userCollectionTcgdexIds.add(tcgdexCardId);
            }
            location.reload(); // Reload to update the badge
        } else {
            alert('Failed to add card to collection');
        }
    } catch (error) {
        console.error('Error adding to collection:', error);
        alert('Failed to add card to collection');
    }
}

async function addCardToDeck(productId, tcgdexCardId, cardName) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('quantity', 1);
        
        let url;
        if (tcgdexCardId) {
            formData.append('tcgdex_card_id', tcgdexCardId);
            url = `/decks/${deckId}/cards/tcgdex`;
        } else if (productId) {
            formData.append('product_id', productId);
            url = `/decks/${deckId}/cards`;
        } else {
            alert('Invalid card data');
            return;
        }
        
        const response = await fetch(url, {
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

// Rarity Filtering
let activeRarityFilter = null;

function filterByRarity(rarity, totalCards, uniqueCards) {
    const allCards = document.querySelectorAll('.deck-card-item');
    const clearButton = document.getElementById('clear-rarity-filter');
    const rarityItems = document.querySelectorAll('.rarity-filter-item');
    
    // If clicking the same rarity, clear filter
    if (activeRarityFilter === rarity) {
        clearRarityFilter();
        return;
    }
    
    activeRarityFilter = rarity;
    clearButton.classList.remove('hidden');
    
    // Update active state on rarity items
    rarityItems.forEach(item => {
        if (item.dataset.rarity === rarity) {
            item.classList.add('bg-blue-600/20', 'border', 'border-blue-500/50');
        } else {
            item.classList.remove('bg-blue-600/20', 'border', 'border-blue-500/50');
            item.classList.add('opacity-50');
        }
    });
    
    // Filter cards and calculate stats
    let visibleCount = 0;
    let visibleUniqueCount = 0;
    let visibleTotalValue = 0;
    const seenCards = new Set();
    
    allCards.forEach(card => {
        if (card.dataset.rarity === rarity) {
            card.classList.remove('hidden');
            
            const quantity = parseInt(card.dataset.quantity) || 1;
            const price = parseFloat(card.dataset.cardPrice) || 0;
            const productId = card.dataset.productId;
            
            visibleCount += quantity;
            
            // Count unique cards
            if (productId && !seenCards.has(productId)) {
                visibleUniqueCount++;
                seenCards.add(productId);
            }
            
            visibleTotalValue += price * quantity;
        } else {
            card.classList.add('hidden');
        }
    });
    
    // Update top stats
    updateStatsDisplay(totalCards, uniqueCards, visibleTotalValue);
}

function clearRarityFilter() {
    activeRarityFilter = null;
    const allCards = document.querySelectorAll('.deck-card-item');
    const clearButton = document.getElementById('clear-rarity-filter');
    const rarityItems = document.querySelectorAll('.rarity-filter-item');
    
    clearButton.classList.add('hidden');
    
    // Remove active state from rarity items
    rarityItems.forEach(item => {
        item.classList.remove('bg-blue-600/20', 'border', 'border-blue-500/50', 'opacity-50');
    });
    
    // Show all cards
    allCards.forEach(card => card.classList.remove('hidden'));
    
    // Restore original stats
    const totalCards = {{ $deck->totalCards() }};
    const uniqueCards = {{ $deck->deckCards->count() }};
    const totalValue = {{ $topStats['total_value_eur'] ?? 0 }};
    
    updateStatsDisplay(totalCards, uniqueCards, totalValue);
}

function updateStatsDisplay(totalCards, uniqueCards, estimatedValue) {
    // Update Total Cards
    const totalCardsElement = document.querySelector('[data-stat="total-cards"]');
    if (totalCardsElement) {
        totalCardsElement.textContent = totalCards;
    }
    
    // Update Unique Cards
    const uniqueCardsElement = document.querySelector('[data-stat="unique-cards"]');
    if (uniqueCardsElement) {
        uniqueCardsElement.textContent = uniqueCards;
    }
    
    // Update Estimated Value (EUR - both simple and with preferred currency)
    const valueElementEurSimple = document.querySelector('[data-stat="estimated-value-eur-simple"]');
    if (valueElementEurSimple) {
        valueElementEurSimple.textContent = '€' + estimatedValue.toFixed(2);
    }
    
    const valueElementEur = document.querySelector('[data-stat="estimated-value-eur"]');
    if (valueElementEur) {
        // Keep the same format (symbol + number or number + symbol)
        const currentText = valueElementEur.textContent;
        const hasSymbolFirst = /^[^\d]/.test(currentText);
        const symbol = currentText.match(/[^\d.,\s]+/)?.[0] || '€';
        
        if (hasSymbolFirst) {
            valueElementEur.textContent = symbol + estimatedValue.toFixed(2);
        } else {
            valueElementEur.textContent = estimatedValue.toFixed(2) + ' ' + symbol;
        }
    }
    
    // Update original price EUR
    const originalPriceEur = document.querySelector('[data-stat="original-price-eur"]');
    if (originalPriceEur) {
        originalPriceEur.textContent = estimatedValue.toFixed(2);
    }
    
    // Note: We're only tracking EUR prices from cards, so we update EUR values
    // USD would need separate tracking if needed
}

// Add clear filter button listener
document.getElementById('clear-rarity-filter')?.addEventListener('click', function(e) {
    e.stopPropagation();
    clearRarityFilter();
});

// Photo modal functions for deck cards
const deckPhotos = @json($deckPhotoData ?? []);

function openDeckPhotoModal(deckCardId) {
    const photos = deckPhotos[deckCardId] || [];
    const gallery = document.getElementById('deckPhotoGalleryContent');
    
    if (photos.length === 0) {
        gallery.innerHTML = '<p class="text-gray-400 col-span-full text-center py-8">No photos available</p>';
    } else {
        gallery.innerHTML = photos.map((photo, index) => `
            <div class="relative group">
                <div class="aspect-[245/342] bg-black/50 rounded-lg border border-white/20 overflow-hidden cursor-pointer hover:border-blue-400 transition photo-thumbnail" data-photo-url="${photo.path}" data-index="${index}">
                    <img src="${photo.path}" alt="Card photo" class="w-full h-full object-contain">
                </div>
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition" onclick="event.stopPropagation()">
                    <form method="POST" action="/decks/photos/${photo.id}" onsubmit="return confirm('Delete this photo?');" class="inline">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="p-1 bg-red-600/80 hover:bg-red-600 rounded text-white text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
                <p class="text-xs text-gray-400 mt-1">${photo.uploaded_at}</p>
            </div>
        `).join('');
        
        // Add click listeners to photo thumbnails
        document.querySelectorAll('.photo-thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const photoUrl = this.getAttribute('data-photo-url');
                openDeckLightbox(photoUrl);
            });
        });
    }
    
    document.getElementById('deckPhotoModal').classList.remove('hidden');
}

function closeDeckPhotoModal() {
    document.getElementById('deckPhotoModal').classList.add('hidden');
}

function openDeckLightbox(imagePath) {
    // Close photo modal first
    document.getElementById('deckPhotoModal').classList.add('hidden');
    
    // Create lightbox dynamically and append to body
    const lightbox = document.createElement('div');
    lightbox.id = 'dynamicDeckLightbox';
    lightbox.className = 'fixed inset-0 z-[99999] overflow-hidden';
    lightbox.style.zIndex = '99999';
    lightbox.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/95 transition-opacity"></div>
            <button class="close-deck-lightbox absolute top-4 right-4 text-white hover:text-gray-300 bg-black/50 rounded-full p-2" style="z-index: 100000;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <img src="${imagePath}" alt="Card photo" class="relative max-w-full max-h-screen object-contain" style="z-index: 100000;">
        </div>
    `;
    
    // Click on background or button to close and reopen photo modal
    const closeLightboxFn = function() {
        const lb = document.getElementById('dynamicDeckLightbox');
        if (lb) lb.remove();
        // Reopen photo modal
        document.getElementById('deckPhotoModal').classList.remove('hidden');
    };
    
    lightbox.querySelector('.fixed.inset-0').addEventListener('click', closeLightboxFn);
    lightbox.querySelector('.close-deck-lightbox').addEventListener('click', closeLightboxFn);
    
    document.body.appendChild(lightbox);
}
</script>
@endsection
