@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ __('collection/index.title') }}</h1>
                    <p class="text-gray-400">{{ __('collection/index.subtitle') }}</p>
                </div>
                
                <!-- Card Limit Badge (Free users only) -->
                @if(auth()->user()->isFree())
                    @php
                        $cardLimit = auth()->user()->cardLimit();
                        $currentUsage = auth()->user()->currentCardUsage();
                        $percentUsed = $cardLimit > 0 ? round(($currentUsage / $cardLimit) * 100) : 0;
                        $isNearLimit = $percentUsed >= 80;
                        $isAtLimit = $currentUsage >= $cardLimit;
                    @endphp
                    <div class="text-right">
                        <div class="inline-flex items-center gap-2 px-4 py-2 {{ $isAtLimit ? 'bg-red-500/20 text-red-300' : ($isNearLimit ? 'bg-yellow-500/20 text-yellow-300' : 'bg-blue-500/20 text-blue-300') }} rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="font-semibold">
                                {{ __('limits.cards.free.usage', ['used' => $currentUsage, 'limit' => $cardLimit]) }}
                            </span>
                        </div>
                        @if($isNearLimit)
                            <p class="text-xs text-gray-400 mt-1">
                                <a href="{{ route('profile.subscription') }}" class="hover:text-blue-400 underline">
                                    {{ __('limits.cards.cta_upgrade') }}
                                </a>
                            </p>
                        @endif
                    </div>
                @endif
            </div>
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

        <!-- Quick Add Card -->
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl mb-6 p-6">
            <h2 class="text-lg font-semibold text-white mb-4">{{ __('collection/index.quick_add_card') }}</h2>
            <div class="relative" x-data="{ searchOpen: false }" @click.away="searchOpen = false">
                <input 
                    type="text" 
                    id="collection-card-search" 
                    placeholder="{{ __('collection/index.search_placeholder') }}"
                    class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @focus="searchOpen = true"
                >
                <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <div id="collection-search-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                    <!-- Results will be inserted here by JS -->
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @if(auth()->user()->canSeeCollectionMiniStats())
            <!-- Rarity Distribution -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center gap-4 mb-3">
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">{{ __('collection/index.rarity_distribution') }}</p>
                        <p class="text-white text-2xl font-bold">{{ $topStats['rarity_distribution']->count() }} {{ __('collection/index.rarity_types') }}</p>
                    </div>
                </div>
                @if($topStats['rarity_distribution']->isNotEmpty())
                <div class="space-y-1">
                    @foreach($topStats['rarity_distribution']->take(3) as $rarity)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">{{ $rarity->rarity ?: 'Unknown' }}</span>
                        <span class="text-white font-medium">{{ $rarity->total_quantity }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Foil Percentage -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center gap-4 mb-3">
                    <div class="bg-yellow-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">{{ __('collection/index.foil_cards') }}</p>
                        <p class="text-white text-2xl font-bold">{{ $topStats['foil_percentage'] }}%</p>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $topStats['foil_percentage'] }}%"></div>
                    </div>
                    <p class="text-gray-400 text-xs mt-1">{{ number_format($topStats['foil_count']) }} {{ __('collection/index.foil_of_cards', ['total' => number_format($topStats['total_count'])]) }}</p>
                </div>
            </div>

            <!-- Set Completion -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center gap-4 mb-3">
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">{{ __('collection/index.top_set') }}</p>
                        @if($topStats['set_completion'])
                        <p class="text-white text-lg font-bold">{{ $topStats['set_completion']['percentage'] }}%</p>
                        @else
                        <p class="text-white text-lg font-bold">-</p>
                        @endif
                    </div>
                </div>
                @if($topStats['set_completion'])
                <div class="mt-2">
                    <p class="text-gray-300 text-sm truncate">{{ $topStats['set_completion']['name'] }}</p>
                    <p class="text-gray-400 text-xs">{{ __('collection/index.set_completion_cards', ['owned' => $topStats['set_completion']['owned'], 'total' => $topStats['set_completion']['total']]) }}</p>
                </div>
                @endif
            </div>
            @else
            <!-- Free tier - Show upsell badge -->
            <div class="md:col-span-3 bg-[#161615] border border-white/15 rounded-xl p-8 text-center">
                <div class="flex flex-col items-center">
                    <div class="bg-blue-500/20 p-4 rounded-full mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">{{ __('stats.upsell.collection_free_title') }}</h3>
                    <p class="text-gray-400 mb-6 max-w-2xl">{{ __('stats.upsell.collection_free_body') }}</p>
                    <a href="{{ route('billing.index') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        {{ __('stats.upsell.cta_upgrade') }}
                    </a>
                </div>
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdvanced() && !auth()->user()->isPremium())
        <!-- Advanced tier - Show upsell badge for Premium Statistics tab -->
        <div class="mb-8 bg-gradient-to-r from-purple-500/10 to-blue-500/10 border border-purple-500/20 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold">{{ __('stats.upsell.collection_advanced_title') }}</h4>
                        <p class="text-gray-400 text-sm">{{ __('stats.upsell.collection_advanced_body') }}</p>
                    </div>
                </div>
                <a href="{{ route('billing.index') }}" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition flex-shrink-0">
                    {{ __('stats.upsell.cta_upgrade') }}
                </a>
            </div>
        </div>
        @endif

        @can('seePrices')
        <!-- Collection Valuation -->
        @php
            $user = auth()->user();
            $preferredCurrency = $user->preferred_currency;
            $defaultCurrency = $preferredCurrency ?: 'EUR';
            
            // If user has a preferred currency, convert the prices
            if ($preferredCurrency) {
                $displayValueEur = \App\Services\CurrencyService::convert($valuation['total_value_eur'], 'EUR', $preferredCurrency);
                $displayValueUsd = \App\Services\CurrencyService::convert($valuation['total_value_usd'], 'USD', $preferredCurrency);
                $currencySymbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
            } else {
                $displayValueEur = $valuation['total_value_eur'];
                $displayValueUsd = $valuation['total_value_usd'];
                $currencySymbol = null;
            }
        @endphp
        <div class="bg-[#161615] border border-white/15 rounded-xl p-6 mb-6" x-data="{ 
            currency: localStorage.getItem('collectionCurrency') || '{{ $defaultCurrency }}',
            preferredCurrency: '{{ $preferredCurrency }}',
            setCurrency(curr) {
                this.currency = curr;
                localStorage.setItem('collectionCurrency', curr);
            }
        }">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">{{ __('collection/index.collection_value') }}</p>
                        
                        @if($preferredCurrency)
                            <!-- User has preferred currency - show converted price with original -->
                            <div x-show="currency === 'EUR'">
                                <p class="text-white text-3xl font-bold">
                                    @php
                                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                        $formatted = number_format($displayValueEur, 2);
                                        // For EUR, USD, etc. - symbol before
                                        if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                            echo "{$symbol}{$formatted}";
                                        } else {
                                            // For Nordic currencies - symbol after
                                            echo "{$formatted} {$symbol}";
                                        }
                                    @endphp
                                </p>
                                <p class="text-gray-500 text-xs">{{ __('collection/index.original_price') }}: €{{ number_format($valuation['total_value_eur'], 2) }}</p>
                            </div>
                            <div x-show="currency === 'USD'">
                                <p class="text-white text-3xl font-bold">
                                    @php
                                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                        $formatted = number_format($displayValueUsd, 2);
                                        // For EUR, USD, etc. - symbol before
                                        if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                            echo "{$symbol}{$formatted}";
                                        } else {
                                            // For Nordic currencies - symbol after
                                            echo "{$formatted} {$symbol}";
                                        }
                                    @endphp
                                </p>
                                <p class="text-gray-500 text-xs">{{ __('collection/index.original_price') }}: ${{ number_format($valuation['total_value_usd'], 2) }}</p>
                            </div>
                        @else
                            <!-- No preferred currency - show default EUR/USD -->
                            <p class="text-white text-3xl font-bold" x-show="currency === 'EUR'">
                                €{{ number_format($valuation['total_value_eur'], 2) }}
                            </p>
                            <p class="text-white text-3xl font-bold" x-show="currency === 'USD'">
                                ${{ number_format($valuation['total_value_usd'], 2) }}
                            </p>
                        @endif
                        
                        <p class="text-gray-500 text-xs mt-1" x-show="currency === 'EUR'">
                            {{ $valuation['cards_with_prices_eur'] }}/{{ $stats['unique_cards'] }} {{ __('collection/index.cards_with_prices') }}
                        </p>
                        <p class="text-gray-500 text-xs mt-1" x-show="currency === 'USD'">
                            {{ $valuation['cards_with_prices_usd'] }}/{{ $stats['unique_cards'] }} {{ __('collection/index.cards_with_prices') }}
                        </p>
                    </div>
                </div>
                
                <!-- Currency Toggle -->
                <div class="inline-flex bg-black/50 border border-white/15 rounded-lg p-1">
                    <button 
                        @click="setCurrency('EUR')"
                        :class="currency === 'EUR' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="px-4 py-2 rounded-md font-medium transition"
                    >
                        EUR
                    </button>
                    <button 
                        @click="setCurrency('USD')"
                        :class="currency === 'USD' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                        class="px-4 py-2 rounded-md font-medium transition"
                    >
                        USD
                    </button>
                </div>
            </div>
            <div class="text-xs text-gray-500" x-show="currency === 'EUR'">
                {{ __('collection/index.prices_from_cardmarket') }}
            </div>
            <div class="text-xs text-gray-500" x-show="currency === 'USD'">
                {{ __('collection/index.prices_from_tcgplayer') }}
            </div>
        </div>
        @else
        <!-- Prices Hidden for Free Users -->
        <div class="bg-[#161615] border border-white/15 rounded-xl p-6 mb-6">
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

        <!-- Tabs -->
        <div class="mb-6" x-data="{ activeTab: 'cards' }">
            <div class="border-b border-white/15">
                <nav class="flex gap-4">
                    <button 
                        @click="activeTab = 'cards'"
                        :class="activeTab === 'cards' ? 'border-blue-500 text-white' : 'border-transparent text-gray-400 hover:text-white'"
                        class="py-3 px-4 border-b-2 font-medium transition"
                    >
                        {{ __('collection/index.tab_cards') }} ({{ $collection->total() }})
                    </button>
                    @if(auth()->user()->canSeeCollectionStatisticsTab())
                    <button 
                        @click="activeTab = 'statistics'"
                        :class="activeTab === 'statistics' ? 'border-blue-500 text-white' : 'border-transparent text-gray-400 hover:text-white'"
                        class="py-3 px-4 border-b-2 font-medium transition"
                    >
                        {{ __('collection/index.tab_statistics') }}
                    </button>
                    @endif
                </nav>
            </div>

            <!-- Cards Tab -->
            <div x-show="activeTab === 'cards'" class="mt-6">
        @if($collection->isEmpty())
        <!-- Empty State -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-12 text-center">
            <svg class="w-20 h-20 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-white text-xl font-semibold mb-2">{{ __('collection/index.empty_title') }}</h3>
            <p class="text-gray-400 mb-6">{{ __('collection/index.empty_text') }}</p>
            <a href="{{ route('tcg.expansions.index') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                {{ __('collection/index.browse_cards') }}
            </a>
        </div>
        @else
        <!-- Collection Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($collection as $item)
            <div class="bg-[#161615] border border-white/15 rounded-lg overflow-hidden hover:border-white/30 transition group">
                <a href="{{ route('tcg.cards.show', $item->product_id) }}" class="block">
                    <div class="aspect-[245/342] bg-black/50 relative">
                        @php
                            $displayImage = $item->card->hd_image_url ?? $item->card->image_url;
                        @endphp
                        @if($displayImage)
                        <img src="{{ $displayImage }}" alt="{{ $item->card->name }}" class="w-full h-full object-cover" onerror="this.src='{{ $item->card->image_url }}'">
                        @if($item->card->hd_image_url)
                            <div class="absolute top-2 right-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-blue-500/80 text-white rounded">
                                    HD
                                </span>
                            </div>
                        @endif
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        @endif
                    </div>
                </a>
                <div class="p-3">
                    <h4 class="text-white text-sm font-semibold truncate">{{ $item->card->name }}</h4>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-gray-400 text-xs">{{ __('collection/index.qty_label') }}: {{ $item->quantity }}</span>
                        @if($item->is_foil)
                        <span class="text-yellow-400 text-xs flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            {{ __('collection/index.foil') }}
                        </span>
                        @endif
                    </div>
                    @if($item->condition)
                    <span class="inline-block mt-1 text-xs px-2 py-0.5 bg-white/10 rounded text-gray-300">
                        {{ ucfirst(str_replace('_', ' ', $item->condition)) }}
                    </span>
                    @endif
                    
                    <!-- Photo Upload Section (Premium only) -->
                    @can('uploadCardPhotos')
                    <div class="mt-2 border-t border-white/10 pt-2">
                        @if($item->photos->count() > 0)
                            <!-- Show photos count and link -->
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs text-blue-400">{{ $item->photos->count() }} {{ $item->photos->count() === 1 ? 'photo' : 'photos' }}</span>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('collection.photos.upload', $item) }}" enctype="multipart/form-data" class="relative">
                            @csrf
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" id="photo-{{ $item->id }}" onchange="this.form.submit()">
                            <label for="photo-{{ $item->id }}" class="w-full text-xs px-2 py-1 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded transition cursor-pointer flex items-center justify-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                {{ __('photos.upload.button') }}
                            </label>
                        </form>
                    </div>
                    @else
                        @if(!auth()->user()->isPremium())
                        <div class="mt-2 border-t border-white/10 pt-2">
                            <div class="relative group">
                                <button disabled class="w-full text-xs px-2 py-1 bg-gray-600/20 text-gray-500 rounded cursor-not-allowed flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    {{ __('photos.upload.button') }}
                                </button>
                                <div class="absolute bottom-full left-0 mb-2 w-48 bg-gray-800 text-white text-xs rounded-lg p-2 opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                                    <p class="font-semibold mb-1">{{ __('photos.upload.not_allowed.title') }}</p>
                                    <p class="text-gray-300 text-[10px]">{{ __('photos.upload.not_allowed.body') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endcan
                    
                    <form method="POST" action="{{ route('collection.remove', $item) }}" class="mt-2" onsubmit="return confirm('{{ __('collection/index.confirm_remove') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-xs px-2 py-1 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded transition">
                            {{ __('collection/index.remove') }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $collection->links() }}
        </div>
        @endif
            </div>

            @if(auth()->user()->canSeeCollectionStatisticsTab())
            <!-- Statistics Tab -->
            <div x-show="activeTab === 'statistics'" class="mt-6">
                <div class="space-y-6">
                    <!-- Row 1: Rarity & Condition Distribution -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Rarity Distribution -->
                        <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                            <h3 class="text-white text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                                {{ __('collection/index.rarity_distribution') }}
                            </h3>
                            <div class="space-y-3">
                                @forelse($topStats['rarity_distribution'] as $rarity)
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-300">{{ $rarity->rarity ?: 'Unknown' }}</span>
                                    <div class="flex items-center gap-3">
                                        <div class="w-32 bg-gray-700 rounded-full h-2">
                                            @php
                                                $percentage = ($rarity->total_quantity / $stats['total_cards']) * 100;
                                            @endphp
                                            <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-white font-medium w-12 text-right">{{ $rarity->total_quantity }}</span>
                                    </div>
                                </div>
                                @empty
                                <p class="text-gray-400 text-sm">{{ __('collection/index.no_rarity_data') }}</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Condition Distribution -->
                        <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                            <h3 class="text-white text-lg font-semibold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('collection/index.condition_distribution') }}
                            </h3>
                            <div class="space-y-3">
                                @forelse($detailedStats['condition_distribution'] as $condition)
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-300">{{ ucfirst(str_replace('_', ' ', $condition->condition ?: 'Standard')) }}</span>
                                    <div class="flex items-center gap-3">
                                        <div class="w-32 bg-gray-700 rounded-full h-2">
                                            @php
                                                $percentage = ($condition->total_quantity / $stats['total_cards']) * 100;
                                            @endphp
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-white font-medium w-12 text-right">{{ $condition->total_quantity }}</span>
                                    </div>
                                </div>
                                @empty
                                <p class="text-gray-400 text-sm">{{ __('collection/index.no_condition_data') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Set Completion -->
                    <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                        <h3 class="text-white text-lg font-semibold mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            {{ __('collection/index.top_5_sets') }}
                        </h3>
                        <div class="space-y-4">
                            @forelse($detailedStats['top_sets'] as $set)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-gray-300 font-medium">{{ $set->name }}</span>
                                    <span class="text-white font-bold">{{ $set->completion_percentage }}%</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $set->completion_percentage }}%"></div>
                                    </div>
                                    <span class="text-gray-400 text-sm">{{ $set->owned_count }}/{{ $set->total_in_set }}</span>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-400 text-sm">{{ __('collection/index.no_set_data') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Row 3: Quick Stats -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-[#161615] border border-white/15 rounded-xl p-6 text-center">
                            <div class="bg-blue-500/20 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 text-sm">{{ __('collection/index.different_sets') }}</p>
                            <p class="text-white text-2xl font-bold mt-1">{{ $detailedStats['total_sets'] }}</p>
                        </div>

                        <div class="bg-[#161615] border border-white/15 rounded-xl p-6 text-center">
                            <div class="bg-purple-500/20 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 text-sm">{{ __('collection/index.with_notes') }}</p>
                            <p class="text-white text-2xl font-bold mt-1">{{ $detailedStats['cards_with_notes'] }}</p>
                        </div>

                        <div class="bg-[#161615] border border-white/15 rounded-xl p-6 text-center">
                            <div class="bg-orange-500/20 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 text-sm">{{ __('collection/index.duplicates') }}</p>
                            <p class="text-white text-2xl font-bold mt-1">{{ $detailedStats['duplicate_cards'] }}</p>
                        </div>

                        <div class="bg-[#161615] border border-white/15 rounded-xl p-6 text-center">
                            <div class="bg-yellow-500/20 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 text-sm">{{ __('collection/index.avg_per_set') }}</p>
                            <p class="text-white text-2xl font-bold mt-1">{{ $detailedStats['total_sets'] > 0 ? round($stats['unique_cards'] / $detailedStats['total_sets'], 1) : 0 }}</p>
                        </div>
                    </div>

                    <!-- Row 4: Timeline -->
                    @if($detailedStats['timeline']->isNotEmpty())
                    <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                        <h3 class="text-white text-lg font-semibold mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            {{ __('collection/index.collection_growth') }}
                        </h3>
                        <div class="grid grid-cols-6 gap-2">
                            @foreach($detailedStats['timeline'] as $month)
                            <div class="text-center">
                                <div class="h-32 flex items-end justify-center">
                                    @php
                                        $maxCount = $detailedStats['timeline']->max('count');
                                        $heightPercentage = $maxCount > 0 ? ($month->count / $maxCount) * 100 : 0;
                                    @endphp
                                    <div class="w-full bg-blue-500 rounded-t" style="height: {{ $heightPercentage }}%"></div>
                                </div>
                                <p class="text-white font-medium mt-2">{{ $month->count }}</p>
                                <p class="text-gray-400 text-xs">{{ \Carbon\Carbon::parse($month->month . '-01')->format('M Y') }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
            @endif
    </div>
</div>

<!-- Add to Collection Modal -->
<div id="quickAddModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/75 transition-opacity" onclick="closeQuickAddModal()"></div>
        <div class="relative bg-[#161615] border border-white/15 rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white" id="modalCardName">{{ __('collection/index.modal_add_card') }}</h3>
                <button onclick="closeQuickAddModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="quickAddForm" method="POST" action="{{ route('collection.add') }}">
                @csrf
                <input type="hidden" name="product_id" id="quickAddProductId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('collection/index.modal_quantity') }}</label>
                        <input type="number" name="quantity" value="1" min="1" max="99" class="w-full px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('collection/index.modal_condition') }}</label>
                        <select name="condition" class="w-full px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white">
                            <option value="">{{ __('collection/index.modal_condition_standard') }}</option>
                            <option value="mint">{{ __('collection/index.modal_condition_mint') }}</option>
                            <option value="near_mint">{{ __('collection/index.modal_condition_near_mint') }}</option>
                            <option value="excellent">{{ __('collection/index.modal_condition_excellent') }}</option>
                            <option value="good">{{ __('collection/index.modal_condition_good') }}</option>
                            <option value="light_played">{{ __('collection/index.modal_condition_light_played') }}</option>
                            <option value="played">{{ __('collection/index.modal_condition_played') }}</option>
                            <option value="poor">{{ __('collection/index.modal_condition_poor') }}</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_foil" value="1" id="quickAddFoil" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded">
                        <label for="quickAddFoil" class="ml-2 text-sm text-gray-300">{{ __('collection/index.modal_foil') }}</label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('collection/index.modal_notes') }}</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeQuickAddModal()" class="flex-1 px-4 py-2 bg-white/10 hover:bg-white/20 text-gray-300 rounded-lg transition">
                        {{ __('collection/index.modal_cancel') }}
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        {{ __('collection/index.modal_submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Collection search
const collectionSearchInput = document.getElementById('collection-card-search');
const collectionSearchDropdown = document.getElementById('collection-search-dropdown');
let searchDebounceTimer = null;
let currentSearchRequest = 0;

collectionSearchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    
    clearTimeout(searchDebounceTimer);
    
    if (query.length < 2) {
        collectionSearchDropdown.classList.add('hidden');
        collectionSearchDropdown.innerHTML = '';
        return;
    }
    
    searchDebounceTimer = setTimeout(() => {
        searchCards(query);
    }, 300);
});

async function searchCards(query) {
    const requestId = ++currentSearchRequest;
    
    try {
        const response = await fetch(`/api/search/cards?q=${encodeURIComponent(query)}`);
        
        if (requestId !== currentSearchRequest) return;
        
        const data = await response.json();
        
        if (data.length === 0) {
            collectionSearchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">{{ __('collection/index.no_cards_found') }}</div>';
            collectionSearchDropdown.classList.remove('hidden');
            return;
        }
        
        const resultsHTML = data.map(card => `
            <div class="px-4 py-3 hover:bg-white/10 cursor-pointer border-b border-white/10 last:border-b-0 flex items-center gap-3 search-card-result"
                 data-product-id="${card.product_id}"
                 data-card-name="${escapeHtml(card.name)}">
                <div class="flex-shrink-0 w-12 h-16 bg-black/50 rounded overflow-hidden">
                    ${card.image_url ? `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-cover">` : ''}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white font-medium truncate">${escapeHtml(card.name)}</div>
                    <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) : ''}</div>
                </div>
                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
        `).join('');
        
        collectionSearchDropdown.innerHTML = resultsHTML;
        collectionSearchDropdown.classList.remove('hidden');
        
        // Add click event listeners to search results
        document.querySelectorAll('.search-card-result').forEach(element => {
            element.addEventListener('click', function() {
                const productId = parseInt(this.dataset.productId);
                const cardName = this.dataset.cardName;
                openQuickAddModal(productId, cardName);
            });
        });
    } catch (error) {
        console.error('Search error:', error);
    }
}

function openQuickAddModal(productId, cardName) {
    document.getElementById('quickAddProductId').value = productId;
    document.getElementById('modalCardName').textContent = cardName;
    document.getElementById('quickAddModal').classList.remove('hidden');
    collectionSearchDropdown.classList.add('hidden');
    collectionSearchInput.value = '';
}

function closeQuickAddModal() {
    document.getElementById('quickAddModal').classList.add('hidden');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
