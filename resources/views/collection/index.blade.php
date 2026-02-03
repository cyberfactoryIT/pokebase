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
                        <a href="{{ route('collection.index', ['rarity' => $rarity->rarity]) }}" class="text-gray-400 hover:text-white hover:underline transition cursor-pointer">
                            {{ $rarity->rarity ?: 'Unknown' }}
                        </a>
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
            <!-- Free tier - Show combined upsell badge -->
            <div class="md:col-span-3 bg-[#161615] border border-white/15 rounded-xl p-8">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Statistics Feature -->
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-500/20 p-4 rounded-full mb-4">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ __('stats.upsell.collection_free_title') }}</h3>
                        <p class="text-gray-400 text-sm">{{ __('stats.upsell.collection_free_body') }}</p>
                    </div>
                    
                    <!-- Prices Feature -->
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-500/20 p-4 rounded-full mb-4">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ __('prices.hidden.title') }}</h3>
                        <p class="text-gray-400 text-sm">{{ __('prices.hidden.body') }}</p>
                    </div>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center mt-6 pt-6 border-t border-white/10">
                    <a href="{{ route('billing.index') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition text-center">
                        {{ __('stats.upsell.cta_upgrade') }}
                    </a>
                    <span class="text-gray-400 flex items-center justify-center">{{ __('prices.hidden.or') }}</span>
                    <a href="{{ route('deck-evaluation.packages.index') }}" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition text-center">
                        {{ __('prices.hidden.cta_deck_evaluation') }}
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
                        <svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="8" cy="8" r="4" opacity="0.6"/>
                            <circle cx="12" cy="12" r="4" opacity="0.8"/>
                            <circle cx="16" cy="16" r="4"/>
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
                
                <!-- Letter Filter -->
                <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl mb-6 p-6">
                    <form method="GET" action="{{ route('collection.index') }}">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-white">Filters</h2>
                            @if(request()->hasAny(['sort', 'letter', 'set', 'rarity', 'min_price', 'max_price']))
                                <a href="{{ route('collection.index') }}" class="text-sm text-gray-400 hover:text-white transition flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Clear all filters
                                </a>
                            @endif
                        </div>

                        <!-- Active Filters -->
                        @if(request()->hasAny(['sort', 'letter', 'set', 'rarity', 'min_price', 'max_price']))
                            <div class="mb-4 flex flex-wrap gap-2">
                                @if(request('letter'))
                                    <a href="{{ route('collection.index', array_filter(request()->except('letter'))) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-500/20 border border-blue-500/50 text-blue-400 rounded-lg text-sm hover:bg-blue-500/30 transition">
                                        <span>Letter: {{ request('letter') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                @endif
                                @if(request('sort') && request('sort') !== 'newest')
                                    <a href="{{ route('collection.index', array_filter(request()->except('sort'))) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-500/20 border border-blue-500/50 text-blue-400 rounded-lg text-sm hover:bg-blue-500/30 transition">
                                        <span>Sort: {{ ucfirst(str_replace('-', ' ', request('sort'))) }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                @endif
                                @if(request('set'))
                                    <a href="{{ route('collection.index', array_filter(request()->except('set'))) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-500/20 border border-blue-500/50 text-blue-400 rounded-lg text-sm hover:bg-blue-500/30 transition">
                                        <span>Set: {{ collect($availableSets)->firstWhere('id', request('set'))['name'] ?? collect($availableSets)->firstWhere('name', request('set'))['name'] ?? request('set') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                @endif
                                @if(request('rarity'))
                                    <a href="{{ route('collection.index', array_filter(request()->except('rarity'))) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-500/20 border border-blue-500/50 text-blue-400 rounded-lg text-sm hover:bg-blue-500/30 transition">
                                        <span>Rarity: {{ request('rarity') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                @endif
                                @if(request()->hasAny(['min_price', 'max_price']))
                                    <a href="{{ route('collection.index', array_filter(request()->except(['min_price', 'max_price']))) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-500/20 border border-blue-500/50 text-blue-400 rounded-lg text-sm hover:bg-blue-500/30 transition">
                                        <span>Price: 
                                            @if(request('min_price')){{ request('min_price') }}@endif
                                            @if(request('min_price') && request('max_price')) - @endif
                                            @if(request('max_price')){{ request('max_price') }}@endif
                                            {{ auth()->user()->preferred_currency ?? 'EUR' }}
                                        </span>
                                        </span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        @endif

                        <div class="space-y-4">
                            <!-- Letter Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Filter by First Letter</label>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(range('A', 'Z') as $letter)
                                        @if(in_array($letter, $availableLetters))
                                            <label class="cursor-pointer">
                                                <input type="checkbox" name="letter" value="{{ $letter }}" 
                                                       {{ request('letter') == $letter ? 'checked' : '' }}
                                                       onchange="this.form.submit()" class="hidden peer">
                                                <span class="inline-block px-2.5 py-1 border border-white/20 rounded
                                                       peer-checked:bg-blue-500 peer-checked:border-blue-500
                                                       hover:border-blue-400 transition text-sm font-medium">
                                                    {{ $letter }}
                                                </span>
                                            </label>
                                        @else
                                            <span class="inline-block px-2.5 py-1 border border-white/10 rounded text-gray-600 text-sm font-medium opacity-40 cursor-not-allowed">
                                                {{ $letter }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Row 1: Sort and Set -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Sort Order -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Sort by</label>
                                    <select name="sort" onchange="this.form.submit()" class="w-full px-4 py-2 bg-black/50 border border-white/20 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                        <option value="a-z" {{ request('sort') === 'a-z' ? 'selected' : '' }}>A → Z</option>
                                        <option value="z-a" {{ request('sort') === 'z-a' ? 'selected' : '' }}>Z → A</option>
                                        @can('seePrices')
                                        <option value="price-asc" {{ request('sort') === 'price-asc' ? 'selected' : '' }}>Price: Low → High</option>
                                        <option value="price-desc" {{ request('sort') === 'price-desc' ? 'selected' : '' }}>Price: High → Low</option>
                                        @endcan
                                    </select>
                                </div>

                                <!-- Filter by Set -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Set</label>
                                    <select name="set" onchange="this.form.submit()" class="w-full px-4 py-2 bg-black/50 border border-white/20 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Sets ({{ count($availableSets) }})</option>
                                        @foreach($availableSets as $set)
                                            <option value="{{ $set['id'] ?? $set['name'] }}" {{ request('set') == ($set['id'] ?? $set['name']) ? 'selected' : '' }}>
                                                {{ $set['name'] }} ({{ $set['card_count'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Rarity and Price -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Filter by Rarity -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Rarity</label>
                                    <select name="rarity" onchange="this.form.submit()" class="w-full px-4 py-2 bg-black/50 border border-white/20 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Rarities ({{ count($availableRarities) }})</option>
                                        @foreach($availableRarities as $rarity)
                                            <option value="{{ $rarity['rarity'] }}" {{ request('rarity') == $rarity['rarity'] ? 'selected' : '' }}>
                                                {{ $rarity['rarity'] ?: 'Unknown' }} ({{ $rarity['card_count'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @can('seePrices')
                                <!-- Price Range Slider -->
                                <div x-data="{
                                    minPrice: {{ request('min_price', $priceRange['min'] ?? 0) }},
                                    maxPrice: {{ request('max_price', $priceRange['max'] ?? 100) }},
                                    rangeMin: {{ $priceRange['min'] ?? 0 }},
                                    rangeMax: {{ $priceRange['max'] ?? 100 }},
                                    currency: '{{ auth()->user()->preferred_currency ?? 'EUR' }}',
                                    submitForm() {
                                        this.$el.closest('form').submit();
                                    }
                                }">
                                    <label class="block text-sm font-medium text-gray-400 mb-3">
                                        Price Range (<span x-text="currency"></span>)
                                    </label>
                                    
                                    <!-- Slider Inputs (hidden) -->
                                    <input type="hidden" name="min_price" :value="minPrice">
                                    <input type="hidden" name="max_price" :value="maxPrice">
                                    
                                    <!-- Range Sliders -->
                                    <div class="relative px-2 mb-6">
                                        <div class="relative h-2 bg-white/10 rounded-full">
                                            <div class="absolute h-2 bg-blue-500 rounded-full" 
                                                 :style="`left: ${((minPrice - rangeMin) / (rangeMax - rangeMin)) * 100}%; right: ${100 - ((maxPrice - rangeMin) / (rangeMax - rangeMin)) * 100}%`"></div>
                                        </div>
                                        <input type="range" 
                                               x-model.number="minPrice" 
                                               :min="rangeMin" 
                                               :max="rangeMax" 
                                               step="0.5"
                                               @input="if(minPrice > maxPrice) maxPrice = minPrice"
                                               class="absolute w-full h-2 top-0 left-0 appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-blue-500 [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-blue-500">
                                        <input type="range" 
                                               x-model.number="maxPrice" 
                                               :min="rangeMin" 
                                               :max="rangeMax" 
                                               step="0.5"
                                               @input="if(maxPrice < minPrice) minPrice = maxPrice"
                                               class="absolute w-full h-2 top-0 left-0 appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-blue-500 [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-blue-500">
                                    </div>
                                    
                                    <!-- Price Display and Apply -->
                                    <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-400">
                                        <span x-text="minPrice.toFixed(2) + ' ' + currency"></span>
                                        <span class="mx-2">-</span>
                                        <span x-text="maxPrice.toFixed(2) + ' ' + currency"></span>
                                    </div>
                                    <button type="button" @click="submitForm()" 
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm">
                                        Apply
                                    </button>
                                </div>
                            </div>
                            @endcan
                            </div>
                        </div>
                    </form>
                </div>

        @if($collection->isEmpty())
        <!-- Empty State -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-12 text-center">
            <svg class="w-20 h-20 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-white text-xl font-semibold mb-2">{{ __('collection/index.empty_title') }}</h3>
            <p class="text-gray-400 mb-6">{{ __('collection/index.empty_text') }}</p>
            @php
                $browseUrl = match($catalogBackend) {
                    'tcgdex' => route('pokemon.sets'),
                    'cmapi' => route('cmapi.sets.index', ['game' => $currentGame->slug ?? 'lorcana']),
                    default => route('tcg.expansions.index')
                };
            @endphp
            <a href="{{ $browseUrl }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                {{ __('collection/index.browse_cards') }}
            </a>
        </div>
        @else
        @php
            // Prepare photo data for JavaScript
            $photoData = [];
            foreach($collection as $item) {
                $photoData[$item->id] = $item->photos->map(function($photo) {
                    return [
                        'id' => $photo->id,
                        'path' => route('collection.photos.serve', $photo->id),
                        'uploaded_at' => $photo->created_at->diffForHumans(),
                    ];
                })->toArray();
            }
        @endphp
        
        <!-- Selection Action Bar (Always Visible) -->
        <div id="selectedCardsBar" class="bg-[#1a1a19] border border-white/15 rounded-xl shadow-xl mb-6 p-4" x-data="{ addToDeckOpen: false }">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-gray-400 text-sm">
                        <span id="selectedCount" class="text-white font-semibold text-lg">0</span> cards selected
                    </span>
                    <div class="flex items-center gap-2">
                        <button onclick="selectAllCards()" class="text-xs px-3 py-1.5 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white rounded transition border border-white/10">
                            Select All
                        </button>
                        <button onclick="clearSelection()" class="text-xs px-3 py-1.5 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white rounded transition border border-white/10">
                            Deselect All
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <button id="addToDeckBtn" @click="addToDeckOpen = !addToDeckOpen" disabled class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add to Deck
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="addToDeckOpen" @click.away="addToDeckOpen = false" x-cloak
                             class="absolute bottom-full left-0 mb-2 w-64 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto">
                            <div class="p-2">
                                @php
                                    $userDecksForSelection = Auth::user()->decks()
                                        ->where('game_id', $currentGame->id ?? null)
                                        ->with('deckCards')
                                        ->latest()
                                        ->get();
                                @endphp
                                
                                @if($userDecksForSelection->isEmpty())
                                    <p class="px-3 py-2 text-sm text-gray-400">No decks available</p>
                                @else
                                    @foreach($userDecksForSelection as $userDeck)
                                        <button type="button" onclick="addSelectedCardsToDeck({{ $userDeck->id }}, '{{ addslashes($userDeck->name) }}')"
                                                class="w-full text-left px-3 py-2 text-sm text-gray-300 hover:bg-white/10 hover:text-white rounded transition flex items-center justify-between group">
                                            <span class="truncate">{{ $userDeck->name }}</span>
                                            <span class="text-xs text-gray-500 group-hover:text-gray-400">({{ $userDeck->deckCards->count() }} cards)</span>
                                        </button>
                                    @endforeach
                                @endif
                                
                                <div class="border-t border-white/10 mt-2 pt-2">
                                    @if(Auth::user()->canCreateAnotherDeck())
                                        <button type="button" onclick="createDeckAndAddSelectedCards()"
                                                class="w-full text-left px-3 py-2 text-sm text-blue-400 hover:bg-white/10 hover:text-blue-300 rounded transition flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Create New Deck
                                        </button>
                                    @else
                                        <a href="{{ route('profile.subscription') }}"
                                           class="block px-3 py-2 text-sm text-orange-400 hover:bg-white/10 hover:text-orange-300 rounded transition">
                                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            Upgrade to create more decks
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($collection as $item)
            @php
                // Determine which card relation to use
                $isTcgdex = !is_null($item->tcgdex_card_id);
                $isCmapi = !is_null($item->cmapi_card_id);
                $isTcgcsv = !$isTcgdex && !$isCmapi;
                
                if ($isTcgdex) {
                    $card = $item->tcgdexCard;
                    $cardName = is_array($card->name) ? ($card->name['en'] ?? 'Unknown') : (is_string($card->name) ? (json_decode($card->name, true)['en'] ?? $card->name) : 'Unknown');
                    $displayImage = $card->image_large_url ?? $card->image_small_url;
                    if ($displayImage && !str_ends_with($displayImage, '.webp')) {
                        $displayImage .= '/high.webp';
                    }
                    $cardUrl = route('pokemon.card', [$card->tcgdex_id]);
                } elseif ($isCmapi) {
                    $card = $item->cmapiCard;
                    $cardName = $card->name ?? 'Unknown';
                    $displayImage = $card->image_large_url ?? $card->image_small_url;
                    $cardUrl = route('cmapi.cards.show', [$currentGame->slug ?? 'lorcana', $card->cmapi_id]);
                } else {
                    $card = $item->card;
                    $cardName = $card->name ?? 'Unknown';
                    $displayImage = $card->hd_image_url ?? $card->image_url;
                    $cardUrl = route('tcg.cards.show', $item->product_id);
                }
            @endphp
            <div class="bg-[#161615] border border-white/15 rounded-lg overflow-hidden hover:border-white/30 transition group">
                <a href="{{ $cardUrl }}" class="block">
                    <div class="aspect-[245/342] bg-black/50 relative">
                        @if($displayImage)
                        <img src="{{ $displayImage }}" alt="{{ $cardName }}" class="w-full h-full object-cover" @if($isTcgcsv && isset($card->image_url)) onerror="this.src='{{ $card->image_url }}'" @endif>
                        @if($isTcgcsv && isset($card->hd_image_url) && $card->hd_image_url)
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
                    <!-- Card name with selection checkbox -->
                    <div class="flex items-center gap-2 mb-2">
                        <input type="checkbox" 
                               class="card-selection-checkbox w-4 h-4 rounded border-2 border-white/30 bg-black/50 checked:bg-blue-600 checked:border-blue-600 cursor-pointer flex-shrink-0"
                               data-collection-id="{{ $item->id }}"
                               data-card-id="{{ $isTcgdex ? $item->tcgdex_card_id : ($isCmapi ? $item->cmapi_card_id : $item->product_id) }}"
                               data-backend="{{ $catalogBackend }}"
                               onchange="toggleCardSelection(this)">
                        <h4 class="text-white text-sm font-semibold truncate flex-1">{{ $cardName }}</h4>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-gray-400 text-xs">{{ __('collection/index.qty_label') }}: {{ $item->quantity }}</span>
                            @if(auth()->user()->isAdvanced() || auth()->user()->isPremium())
                                @if($item->cached_price && $item->cached_price > 0)
                                    @php
                                        $user = auth()->user();
                                        $preferredCurrency = $user->preferred_currency ?? 'EUR';
                                        $needsConversion = $preferredCurrency && $preferredCurrency !== 'EUR';
                                        
                                        if ($needsConversion) {
                                            $convertedPrice = \App\Services\CurrencyService::convert($item->cached_price, 'EUR', $preferredCurrency);
                                            $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                            $symbolAfter = in_array($preferredCurrency, ['DKK', 'SEK', 'NOK']);
                                            $formatted = $symbolAfter ? number_format($convertedPrice, 2) . ' ' . $symbol : $symbol . number_format($convertedPrice, 2);
                                        } else {
                                            $formatted = '€' . number_format($item->cached_price, 2);
                                        }
                                    @endphp
                                    <span class="text-green-400 text-xs font-semibold">
                                        {{ $formatted }} {{ __('collection/index.each') }}
                                        @if($needsConversion)
                                            <span class="text-gray-500">(€{{ number_format($item->cached_price, 2) }})</span>
                                        @endif
                                    </span>
                                @endif
                            @endif
                        </div>
                        @if($item->is_foil)
                        <span class="text-yellow-400 text-xs flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            {{ __('collection/index.foil') }}
                        </span>
                        @endif
                    </div>
                    
                    <!-- Photo Upload Section (Premium only) -->
                    @can('uploadCardPhotos')
                    <div class="mt-2 border-t border-white/10 pt-2">
                        @if($item->photos->count() > 0)
                            <!-- Show photos count and link -->
                            <button onclick="openPhotoModal({{ $item->id }})" class="flex items-center gap-2 mb-1 w-full text-left hover:bg-white/5 px-2 py-1 rounded transition">
                                <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs text-blue-400 underline">{{ $item->photos->count() }} {{ $item->photos->count() === 1 ? 'photo' : 'photos' }}</span>
                            </button>
                        @endif
                        <form method="POST" action="{{ route('collection.photos.upload', $item) }}" enctype="multipart/form-data" class="relative">
                            @csrf
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" id="photo-{{ $item->id }}" onchange="showUploadLoader(this.form)">
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
                                    <a href="{{ route('collection.index', ['rarity' => $rarity->rarity]) }}" class="text-gray-300 hover:text-white hover:underline transition cursor-pointer font-medium">
                                        {{ $rarity->rarity ?: 'Unknown' }}
                                    </a>
                                    <div class="flex items-center gap-3">
                                        <div class="w-32 bg-gray-700 rounded-full h-2">
                                            @php
                                                $percentage = $stats['total_cards'] > 0 ? ($rarity->total_quantity / $stats['total_cards']) * 100 : 0;
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
                            @if($rarityInsight)
                            <p class="text-gray-400 text-sm mt-4 italic border-t border-white/10 pt-3">{{ $rarityInsight }}</p>
                            @endif
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
                            @if($conditionInsight)
                            <p class="text-gray-400 text-sm mt-4 italic border-t border-white/10 pt-3">{{ $conditionInsight }}</p>
                            @endif
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
                        @if($setsInsight)
                        <p class="text-gray-400 text-sm mb-4 italic">{{ $setsInsight }}</p>
                        @endif
                        <div class="space-y-4">
                            @forelse($detailedStats['top_sets'] as $set)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-300 font-medium">{{ $set->name }}</span>
                                        @if($focusSet && $focusSet['group_id'] == $set->group_id)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full border border-green-500/30">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ __('stats_insights.focus_set.badge') }}
                                        </span>
                                        @endif
                                    </div>
                                    <span class="text-white font-bold">{{ $set->completion_percentage }}%</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $set->completion_percentage }}%"></div>
                                    </div>
                                    <span class="text-gray-400 text-sm">{{ $set->owned_count }}/{{ $set->total_in_set }}</span>
                                </div>
                                @if($focusSet && $focusSet['group_id'] == $set->group_id)
                                <p class="text-xs text-green-400/80 mt-1 ml-1">{{ __('stats_insights.focus_set.helper') }}</p>
                                @endif
                            </div>
                            @empty
                            <p class="text-gray-400 text-sm">{{ __('collection/index.no_set_data') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Row 3: Quick Stats -->
                    <h3 class="text-lg font-semibold text-white">{{ __('stats_insights.section_labels.collection_overview') }}</h3>
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
                <input type="hidden" name="tcgdex_card_id" id="quickAddTcgdexCardId">
                
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

<!-- Photo Gallery Modal -->
<div id="photoModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/90 transition-opacity" onclick="closePhotoModal()"></div>
        <div class="relative bg-[#161615] border border-white/15 rounded-xl shadow-xl max-w-4xl w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white">Card Photos</h3>
                <button onclick="closePhotoModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="photoGalleryContent" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Photos will be loaded here -->
            </div>
            
            <!-- Upload Photo Form (hidden) -->
            <form id="photoUploadForm" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" id="photoUploadInput" name="photo" accept="image/*" onchange="handlePhotoUpload(event)">
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Modal for full size photo -->
<div id="lightboxModal" class="hidden fixed inset-0 z-[9999] overflow-hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/95 transition-opacity" onclick="closeLightbox()"></div>
        <button onclick="closeLightbox()" class="absolute top-4 right-4 z-[10000] text-white hover:text-gray-300 bg-black/50 rounded-full p-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img id="lightboxImage" src="" alt="Card photo" class="relative z-[10000] max-w-full max-h-screen object-contain">
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
                 data-product-id="${card.product_id || ''}"
                 data-tcgdex-card-id="${card.tcgdex_card_id || ''}"
                 data-card-name="${escapeHtml(card.name)}">
                <div class="flex-shrink-0 w-12 h-16 bg-black/50 rounded overflow-hidden">
                    ${card.image_url ? `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-cover">` : ''}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white font-medium truncate">${escapeHtml(card.name)}</div>
                    <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) + (card.set_total ? '/' + card.set_total : '') : ''}</div>
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
                const productId = this.dataset.productId ? parseInt(this.dataset.productId) : null;
                const tcgdexCardId = this.dataset.tcgdexCardId ? parseInt(this.dataset.tcgdexCardId) : null;
                const cardName = this.dataset.cardName;
                openQuickAddModal(productId, tcgdexCardId, cardName);
            });
        });
    } catch (error) {
        console.error('Search error:', error);
    }
}

function openQuickAddModal(productId, tcgdexCardId, cardName) {
    // Set appropriate field and form action
    if (tcgdexCardId) {
        document.getElementById('quickAddTcgdexCardId').value = tcgdexCardId;
        document.getElementById('quickAddProductId').value = '';
        document.getElementById('quickAddForm').action = '{{ route('collection.add.tcgdex') }}';
    } else {
        document.getElementById('quickAddProductId').value = productId;
        document.getElementById('quickAddTcgdexCardId').value = '';
        document.getElementById('quickAddForm').action = '{{ route('collection.add') }}';
    }
    
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

// Photo modal functions
const collectionPhotos = @json($photoData ?? []);

let currentCollectionId = null;

function openPhotoModal(collectionId) {
    currentCollectionId = collectionId;
    const photos = collectionPhotos[collectionId] || [];
    const gallery = document.getElementById('photoGalleryContent');
    
    // Build photo grid HTML
    let photosHTML = photos.map((photo, index) => `
        <div class="relative group">
            <div class="aspect-[245/342] bg-black/50 rounded-lg border border-white/20 overflow-hidden cursor-pointer hover:border-blue-400 transition photo-thumbnail" data-photo-url="${photo.path}" data-index="${index}">
                <img src="${photo.path}" alt="Card photo" class="w-full h-full object-contain">
            </div>
            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition" onclick="event.stopPropagation()">
                <form method="POST" action="/collection/photos/${photo.id}" onsubmit="return confirm('Delete this photo?');" class="inline">
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
    
    // Add "Add Photo" button
    photosHTML += `
        <div class="relative group">
            <button onclick="document.getElementById('photoUploadInput').click()" 
                    class="aspect-[245/342] w-full bg-black/30 border-2 border-dashed border-white/20 rounded-lg hover:border-blue-400 hover:bg-black/50 transition flex flex-col items-center justify-center gap-3 text-gray-400 hover:text-blue-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="text-sm font-medium">Add Photo</span>
            </button>
        </div>
    `;
    
    gallery.innerHTML = photosHTML || '<p class="text-gray-400 col-span-full text-center py-8">No photos available</p>';
    
    // Add click listeners to photo thumbnails
    document.querySelectorAll('.photo-thumbnail').forEach(thumb => {
        thumb.addEventListener('click', function() {
            const photoUrl = this.getAttribute('data-photo-url');
            openLightbox(photoUrl);
        });
    });
    
    document.getElementById('photoModal').classList.remove('hidden');
}

function closePhotoModal() {
    document.getElementById('photoModal').classList.add('hidden');
    currentCollectionId = null;
}

async function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (!file || !currentCollectionId) return;
    
    // Show loading overlay
    const gallery = document.getElementById('photoGalleryContent');
    const originalHTML = gallery.innerHTML;
    
    gallery.innerHTML = `
        <div class="col-span-full flex flex-col items-center justify-center py-12">
            <svg class="animate-spin w-12 h-12 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-white font-medium">Uploading photo...</p>
            <p class="text-gray-400 text-sm mt-1">Please wait</p>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('photo', file);
    formData.append('_token', '{{ csrf_token() }}');
    
    try {
        const response = await fetch(`/collection/${currentCollectionId}/photos`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            gallery.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center py-12">
                    <svg class="w-16 h-16 text-green-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-white font-medium">Photo uploaded successfully!</p>
                    <p class="text-gray-400 text-sm mt-1">Refreshing...</p>
                </div>
            `;
            // Reload after 1 second
            setTimeout(() => window.location.reload(), 1000);
        } else {
            // Show error and restore original content
            gallery.innerHTML = originalHTML;
            alert(data.message || 'Error uploading photo');
            // Re-attach event listeners
            openPhotoModal(currentCollectionId);
        }
    } catch (error) {
        console.error('Upload error:', error);
        gallery.innerHTML = originalHTML;
        alert('Error uploading photo. Please try again.');
        // Re-attach event listeners
        openPhotoModal(currentCollectionId);
    }
    
    // Reset file input
    event.target.value = '';
}

function openLightbox(imagePath) {
    // Close photo modal first
    document.getElementById('photoModal').classList.add('hidden');
    
    // Create lightbox dynamically and append to body
    const lightbox = document.createElement('div');
    lightbox.id = 'dynamicLightbox';
    lightbox.className = 'fixed inset-0 z-[99999] overflow-hidden';
    lightbox.style.zIndex = '99999';
    lightbox.innerHTML = `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/95 transition-opacity"></div>
            <button class="close-lightbox absolute top-4 right-4 text-white hover:text-gray-300 bg-black/50 rounded-full p-2" style="z-index: 100000;">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <img src="${imagePath}" alt="Card photo" class="relative max-w-full max-h-screen object-contain" style="z-index: 100000;">
        </div>
    `;
    
    // Click on background or button to close and reopen photo modal
    const closeLightboxFn = function() {
        const lb = document.getElementById('dynamicLightbox');
        if (lb) lb.remove();
        // Reopen photo modal
        document.getElementById('photoModal').classList.remove('hidden');
    };
    
    lightbox.querySelector('.fixed.inset-0').addEventListener('click', closeLightboxFn);
    lightbox.querySelector('.close-lightbox').addEventListener('click', closeLightboxFn);
    
    document.body.appendChild(lightbox);
}

function closeLightbox() {
    document.getElementById('lightboxModal').classList.add('hidden');
}

// Show upload loader for traditional form submissions
function showUploadLoader(form) {
    // Show overlay
    const overlay = document.getElementById('uploadLoadingOverlay');
    if (overlay) {
        overlay.classList.remove('hidden');
    }
    // Submit form
    form.submit();
}

// Add filtered cards to deck
// Card Selection Management
let selectedCards = new Set();

function toggleCardSelection(checkbox) {
    const collectionId = checkbox.dataset.collectionId;
    const cardId = checkbox.dataset.cardId;
    const backend = checkbox.dataset.backend;
    
    if (checkbox.checked) {
        selectedCards.add(JSON.stringify({ collectionId, cardId, backend }));
    } else {
        selectedCards.delete(JSON.stringify({ collectionId, cardId, backend }));
    }
    
    updateSelectionBar();
}

function updateSelectionBar() {
    const count = document.getElementById('selectedCount');
    const btn = document.getElementById('addToDeckBtn');
    
    count.textContent = selectedCards.size;
    
    if (selectedCards.size > 0) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}

function clearSelection() {
    selectedCards.clear();
    document.querySelectorAll('.card-selection-checkbox').forEach(cb => cb.checked = false);
    updateSelectionBar();
}

function selectAllCards() {
    document.querySelectorAll('.card-selection-checkbox').forEach(cb => {
        cb.checked = true;
        const collectionId = cb.dataset.collectionId;
        const cardId = cb.dataset.cardId;
        const backend = cb.dataset.backend;
        selectedCards.add(JSON.stringify({ collectionId, cardId, backend }));
    });
    updateSelectionBar();
}

// Add selected cards to deck
async function addSelectedCardsToDeck(deckId, deckName) {
    if (selectedCards.size === 0) {
        alert('No cards selected');
        return;
    }
    
    if (!confirm(`Add ${selectedCards.size} card(s) to "${deckName}"?`)) {
        return;
    }
    
    try {
        const loadingOverlay = document.getElementById('uploadLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.querySelector('p.text-lg').textContent = 'Adding Cards';
            loadingOverlay.querySelector('p.text-sm').textContent = `Adding ${selectedCards.size} cards to ${deckName}...`;
            loadingOverlay.classList.remove('hidden');
        }
        
        const cards = Array.from(selectedCards).map(json => JSON.parse(json));
        
        const response = await fetch('/api/collection/add-selected-to-deck', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                deck_id: deckId,
                cards: cards
            })
        });
        
        const data = await response.json();
        
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
        
        if (response.ok && data.success) {
            alert(`Successfully added ${data.cards_added} card(s) to ${deckName}!`);
            clearSelection();
            window.location.href = `/decks/${deckId}`;
        } else {
            alert(data.message || 'Failed to add cards to deck');
        }
    } catch (error) {
        console.error('Error adding cards:', error);
        const loadingOverlay = document.getElementById('uploadLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
        alert('Failed to add cards to deck');
    }
}

// Create deck and add selected cards
async function createDeckAndAddSelectedCards() {
    if (selectedCards.size === 0) {
        alert('No cards selected');
        return;
    }
    
    const deckName = prompt('Enter deck name:');
    if (!deckName) return;
    
    try {
        const loadingOverlay = document.getElementById('uploadLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.querySelector('p.text-lg').textContent = 'Creating Deck';
            loadingOverlay.querySelector('p.text-sm').textContent = `Creating "${deckName}" with ${selectedCards.size} cards...`;
            loadingOverlay.classList.remove('hidden');
        }
        
        const cards = Array.from(selectedCards).map(json => JSON.parse(json));
        
        const response = await fetch('/api/collection/create-deck-with-selected', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                deck_name: deckName,
                cards: cards
            })
        });
        
        console.log('Response status:', response.status, 'Response OK:', response.ok);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
        
        if (response.ok && data.success) {
            alert(`Deck "${deckName}" created with ${data.cards_added} card(s)!`);
            clearSelection();
            window.location.href = `/decks/${data.deck_id}`;
        } else {
            console.error('Failed response:', data);
            alert(data.message || 'Failed to create deck');
        }
    } catch (error) {
        console.error('Error creating deck:', error);
        const loadingOverlay = document.getElementById('uploadLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
        alert('Failed to create deck - Check console for details');
    }
}
</script>

<!-- Upload Loading Overlay -->
<div id="uploadLoadingOverlay" class="hidden fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center">
    <div class="bg-[#161615] border border-white/15 rounded-xl p-8 max-w-sm mx-4">
        <div class="flex flex-col items-center">
            <svg class="animate-spin w-16 h-16 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-white font-semibold text-lg mb-2">Uploading Photo</p>
            <p class="text-gray-400 text-sm text-center">Please wait while we upload your photo...</p>
        </div>
    </div>
</div>

@endsection
