@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-400/30 text-green-300 px-4 py-3 rounded-lg flex items-center gap-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-400/30 text-red-300 px-4 py-3 rounded-lg flex items-center gap-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('tcg.expansions.show', $card->group_id) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('tcg/cards/show.back_to') }} {{ $card->group->name ?? __('tcg/cards/show.expansion') }}
            </a>
        </div>

        <!-- Card Detail Layout (Scrydex-like) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Card Image -->
            <div class="space-y-6">
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <div class="aspect-[245/342] max-w-md mx-auto">
                        @php
                            // Usa immagine HD se disponibile, altrimenti fallback su standard
                            $displayImage = $card->hd_image_url ?? $imageUrl;
                        @endphp
                        @if($displayImage)
                            <img 
                                src="{{ $displayImage }}" 
                                alt="{{ $card->name }}"
                                class="w-full h-full object-contain rounded-lg shadow-lg"
                                onerror="this.src='{{ $imageUrl ?? "https://via.placeholder.com/490x684/1a1a19/666?text=No+Image" }}'"
                            >
                            @if($card->hd_image_url)
                                <div class="mt-2 text-center">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-500/20 text-blue-300 rounded-full">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        HD Image
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="w-full h-full bg-black/50 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Details (Unified) -->
                @php
                    $allDetails = [];
                    $fieldOrder = [
                        'set_name' => 'Expansion',
                        'card_number' => 'Number',
                        'rarity' => 'Rarity',
                        'supertype' => 'Card Type',
                        'hp' => 'HP',
                        'artist_name' => 'Artist',
                    ];
                    
                    if ($card->group && $card->group->name) $allDetails['set_name'] = $card->group->name;
                    if ($card->card_number) $allDetails['card_number'] = $card->card_number;
                    if ($card->rarity) $allDetails['rarity'] = $card->rarity;
                    if ($card->supertype) $allDetails['supertype'] = $card->supertype;
                    if ($card->hp) $allDetails['hp'] = $card->hp;
                    if ($card->artist_name) $allDetails['artist_name'] = $card->artist_name;
                    
                    if ($card->raw && is_array($card->raw)) {
                        foreach ($card->raw as $key => $value) {
                            if (!is_array($value) && !is_object($value) && 
                                !in_array($key, ['raw', 'extended_data', 'extendedData', 'imageUrl', 'image_url', 
                                                 'productId', 'categoryId', 'imageCount', 'modifiedOn', 'name', 
                                                 'groupId', 'url', 'cleanName'])) {
                                $allDetails[$key] = $value;
                            }
                        }
                    }
                    
                    if ($card->extended_data && is_array($card->extended_data)) {
                        foreach ($card->extended_data as $item) {
                            if (isset($item['name']) && isset($item['value'])) {
                                $key = strtolower(str_replace(' ', '_', $item['name']));
                                $allDetails[$key] = $item['value'];
                            }
                        }
                    }
                    
                    $orderedDetails = [];
                    foreach ($fieldOrder as $key => $label) {
                        if (isset($allDetails[$key])) {
                            $orderedDetails[$label] = $allDetails[$key];
                            unset($allDetails[$key]);
                        }
                    }
                    foreach ($allDetails as $key => $value) {
                        $label = ucwords(str_replace('_', ' ', $key));
                        $orderedDetails[$label] = $value;
                    }
                @endphp

                @if(count($orderedDetails) > 0)
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('tcg/cards/show.card_details') }}</h2>
                        
                        <dl class="space-y-3">
                            @foreach($orderedDetails as $label => $value)
                                <div class="flex justify-between py-2 border-b border-white/10">
                                    <dt class="text-sm font-medium text-gray-400">{{ $label }}</dt>
                                    <dd class="text-sm text-white text-right max-w-xs">
                                        @if(is_bool($value))
                                            {{ $value ? __('tcg/cards/show.yes') : __('tcg/cards/show.no') }}
                                        @elseif(is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
                                            <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-blue-400 hover:text-blue-300 transition">
                                                <span>Link</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            </a>
                                        @else
                                            {!! nl2br(e($value)) !!}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>

            <!-- Right Column: Card Details -->
            <div class="space-y-6">
                <!-- Card Header -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $card->name }}</h1>
                    
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-300 mb-4">
                        @if($card->group)
                            <a href="{{ route('tcg.expansions.show', $card->group_id) }}" class="inline-flex items-center hover:text-blue-400">
                                @if($card->group->logo_url)
                                    <img src="{{ $card->group->logo_url }}" alt="{{ $card->group->name }}" class="w-5 h-5 mr-2 object-contain">
                                @else
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                @endif
                                {{ $card->group->name }}
                            </a>
                        @endif
                        
                        @if($card->card_number)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/10 border border-white/20 text-gray-200">
                                #{{ $card->card_number }}
                            </span>
                        @endif

                        @if($card->hp)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 border border-red-400/30 text-red-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $card->hp }} HP
                            </span>
                        @endif

                        @if($card->supertype)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 border border-blue-400/30 text-blue-300">
                                {{ $card->supertype }}
                            </span>
                        @endif
                        
                        @if($card->rapidapi_rarity)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 border border-purple-400/30 text-purple-300">
                                {{ $card->rapidapi_rarity }}
                            </span>
                        @elseif($card->rarity)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 border border-purple-400/30 text-purple-300">
                                {{ $card->rarity }}
                            </span>
                        @endif

                        @if($card->artist_name)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 border border-yellow-400/30 text-yellow-300">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                {{ $card->artist_name }}
                            </span>
                        @endif
                    </div>

                    <!-- Collection Actions -->
                    <div class="flex gap-3 pt-4 border-t border-white/10" x-data="{ showDeckModal: false }">
                        <form method="POST" action="{{ route('collection.add') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $card->product_id }}">
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                {{ __('tcg/cards/show.add_to_collection') }}
                            </button>
                        </form>
                        <button type="button" @click="showDeckModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            {{ __('tcg/cards/show.add_to_deck') }}
                        </button>

                        <!-- Deck Selection Modal -->
                        <div x-show="showDeckModal" 
                             x-cloak
                             @click.away="showDeckModal = false"
                             class="fixed inset-0 z-50 overflow-y-auto" 
                             style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4">
                                <div class="fixed inset-0 bg-black/75 transition-opacity" @click="showDeckModal = false"></div>
                                
                                <div class="relative bg-[#161615] border border-white/15 rounded-xl shadow-xl max-w-md w-full p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-xl font-bold text-white">{{ __('tcg/cards/show.modal_deck_title') }}</h3>
                                        <button @click="showDeckModal = false" class="text-gray-400 hover:text-white">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    @php
                                        $userDecks = Auth::user()->decks ?? collect();
                                    @endphp

                                    @if($userDecks->isEmpty())
                                        <p class="text-gray-400 mb-4">{{ __('tcg/cards/show.no_decks_yet') }}</p>
                                        @if(Auth::user()->canCreateAnotherDeck())
                                            <a href="{{ route('decks.create') }}" class="block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-center">
                                                {{ __('tcg/cards/show.create_first_deck') }}
                                            </a>
                                        @else
                                            <a href="{{ route('profile.subscription') }}" class="block w-full px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white rounded-lg transition text-center font-semibold">
                                                {{ __('decks/index.upgrade') }}
                                            </a>
                                        @endif
                                    @else
                                        <div class="space-y-2 max-h-96 overflow-y-auto">
                                            @foreach($userDecks as $deck)
                                                <form method="POST" action="{{ route('decks.cards.add', $deck) }}">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $card->product_id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="w-full text-left px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg transition group">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <div class="font-semibold text-white group-hover:text-blue-400">{{ $deck->name }}</div>
                                                                @if($deck->format)
                                                                    <div class="text-sm text-gray-400">{{ ucfirst($deck->format) }}</div>
                                                                @endif
                                                            </div>
                                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                        </div>
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- External Links -->
                @php
                    $tcgoUrl = $card->rapidapiCard ? $card->rapidapiCard->tcggo_url : null;
                    $tcgplayerUrl = $card->raw && isset($card->raw['url']) ? $card->raw['url'] : null;
                    $cardmarketUrl = $card->rapidapiCard && $card->rapidapiCard->links ? ($card->rapidapiCard->links['cardmarket'] ?? null) : null;
                @endphp
                @if($tcgoUrl || $tcgplayerUrl || $cardmarketUrl)
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">{{ __('tcg/cards/show.external_links') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @if($tcgoUrl)
                            <a href="{{ $tcgoUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-blue-600/20 to-blue-500/20 hover:from-blue-600/30 hover:to-blue-500/30 border border-blue-400/30 rounded-lg transition group">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-white group-hover:text-blue-300 transition">TCGO</div>
                                    <div class="text-xs text-gray-400">View HD Image</div>
                                </div>
                                <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        @endif
                        @if($tcgplayerUrl)
                            <a href="{{ $tcgplayerUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-purple-600/20 to-purple-500/20 hover:from-purple-600/30 hover:to-purple-500/30 border border-purple-400/30 rounded-lg transition group">
                                <div class="flex-shrink-0 w-10 h-10 bg-purple-500/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-white group-hover:text-purple-300 transition">TCGPlayer</div>
                                    <div class="text-xs text-gray-400">Buy on TCGPlayer</div>
                                </div>
                                <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        @endif
                        @if($cardmarketUrl)
                            <a href="{{ $cardmarketUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-orange-600/20 to-orange-500/20 hover:from-orange-600/30 hover:to-orange-500/30 border border-orange-400/30 rounded-lg transition group">
                                <div class="flex-shrink-0 w-10 h-10 bg-orange-500/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-white group-hover:text-orange-300 transition">Cardmarket</div>
                                    <div class="text-xs text-gray-400">Buy on Cardmarket</div>
                                </div>
                                <svg class="w-5 h-5 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Pricing Section -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6" x-data="{ 
                    activeTab: localStorage.getItem('priceTab') || 'us',
                    preferredCurrency: '{{ auth()->user()?->preferred_currency }}'
                }">
                    <!-- Price Toggle Tabs -->
                    <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-2">
                        <h2 class="text-xl font-bold text-white">{{ __('tcg/cards/show.pricing') }}</h2>
                        
                        @can('seePrices')
                        <div class="flex rounded-lg bg-black/50 p-1">
                            <button 
                                @click="activeTab = 'us'; localStorage.setItem('priceTab', 'us')"
                                :class="activeTab === 'us' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-md text-sm font-medium transition flex items-center gap-1.5">
                                <span>🇺🇸</span>
                                <span>{{ __('variants.tcgcsv_prices') }}</span>
                            </button>
                            <button 
                                @click="activeTab = 'eu'; localStorage.setItem('priceTab', 'eu')"
                                :class="activeTab === 'eu' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                                class="px-3 py-1.5 rounded-md text-sm font-medium transition flex items-center gap-1.5">
                                <span>🇪🇺</span>
                                <span>{{ __('variants.eu_prices') }}</span>
                            </button>
                        </div>
                        @endcan
                    </div>
                    
                    @can('seePrices')
                    <!-- US Prices (TCGCSV) -->
                    <div x-show="activeTab === 'us'" x-transition>
                    
                    @if($latestPrice)
                        @php
                            $user = auth()->user();
                            $preferredCurrency = $user?->preferred_currency;
                            
                            // Convert prices if user has preferred currency
                            $marketPriceDisplay = $latestPrice->market_price;
                            $lowPriceDisplay = $latestPrice->low_price;
                            $midPriceDisplay = $latestPrice->mid_price;
                            $highPriceDisplay = $latestPrice->high_price;
                            
                            if ($preferredCurrency) {
                                $marketPriceDisplay = $latestPrice->market_price ? \App\Services\CurrencyService::convert($latestPrice->market_price, 'USD', $preferredCurrency) : 0;
                                $lowPriceDisplay = $latestPrice->low_price ? \App\Services\CurrencyService::convert($latestPrice->low_price, 'USD', $preferredCurrency) : 0;
                                $midPriceDisplay = $latestPrice->mid_price ? \App\Services\CurrencyService::convert($latestPrice->mid_price, 'USD', $preferredCurrency) : 0;
                                $highPriceDisplay = $latestPrice->high_price ? \App\Services\CurrencyService::convert($latestPrice->high_price, 'USD', $preferredCurrency) : 0;
                            }
                        @endphp
                        <div class="grid grid-cols-2 gap-4">
                            @if($latestPrice->market_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.market_price') }}</div>
                                    @if($preferredCurrency)
                                        <div class="text-2xl font-bold text-white">
                                            @php
                                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                $formatted = number_format($marketPriceDisplay, 2);
                                                if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                    echo "{$symbol}{$formatted}";
                                                } else {
                                                    echo "{$formatted} {$symbol}";
                                                }
                                            @endphp
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">(${{ number_format($latestPrice->market_price, 2) }})</div>
                                    @else
                                        <div class="text-2xl font-bold text-white">${{ number_format($latestPrice->market_price, 2) }}</div>
                                    @endif
                                </div>
                            @endif
                            
                            @if($latestPrice->low_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.low_price') }}</div>
                                    @if($preferredCurrency)
                                        <div class="text-xl font-semibold text-gray-200">
                                            @php
                                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                $formatted = number_format($lowPriceDisplay, 2);
                                                if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                    echo "{$symbol}{$formatted}";
                                                } else {
                                                    echo "{$formatted} {$symbol}";
                                                }
                                            @endphp
                                        </div>
                                        <div class="text-xs text-gray-500">(${{ number_format($latestPrice->low_price, 2) }})</div>
                                    @else
                                        <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->low_price, 2) }}</div>
                                    @endif
                                </div>
                            @endif
                            
                            @if($latestPrice->mid_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.mid_price') }}</div>
                                    @if($preferredCurrency)
                                        <div class="text-xl font-semibold text-gray-200">
                                            @php
                                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                $formatted = number_format($midPriceDisplay, 2);
                                                if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                    echo "{$symbol}{$formatted}";
                                                } else {
                                                    echo "{$formatted} {$symbol}";
                                                }
                                            @endphp
                                        </div>
                                        <div class="text-xs text-gray-500">(${{ number_format($latestPrice->mid_price, 2) }})</div>
                                    @else
                                        <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->mid_price, 2) }}</div>
                                    @endif
                                </div>
                            @endif
                            
                            @if($latestPrice->high_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.high_price') }}</div>
                                    @if($preferredCurrency)
                                        <div class="text-xl font-semibold text-gray-200">
                                            @php
                                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                $formatted = number_format($highPriceDisplay, 2);
                                                if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                    echo "{$symbol}{$formatted}";
                                                } else {
                                                    echo "{$formatted} {$symbol}";
                                                }
                                            @endphp
                                        </div>
                                        <div class="text-xs text-gray-500">(${{ number_format($latestPrice->high_price, 2) }})</div>
                                    @else
                                        <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->high_price, 2) }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        @if($latestPrice->printing)
                            <div class="mt-3 text-sm text-gray-300">
                                <span class="font-medium">{{ __('tcg/cards/show.printing') }}:</span> {{ $latestPrice->printing }}
                            </div>
                        @endif
                        
                        <!-- Price Source Citation -->
                        <div class="mt-3 text-xs text-gray-500 border-t border-white/10 pt-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p><strong>{{ __('tcg/cards/show.data_source') }}:</strong> TCGPlayer / TCGCSV</p>
                                    <p class="mt-1">{{ __('tcg/cards/show.last_updated') }}: {{ $latestPrice->snapshot_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>{{ __('tcg/cards/show.pricing_coming_soon') }}</p>
                        </div>
                    @endif
                    </div>
                    
                    <!-- EU Prices (Cardmarket via RapidAPI) -->
                    <div x-show="activeTab === 'eu'" x-transition x-cloak>
                        @php
                            // Get all Cardmarket price data from RapidAPI
                            $rapidapiCard = $card->rapidapiCard;
                            $cardmarketPrices = $rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']) 
                                ? $rapidapiCard->raw_data['prices']['cardmarket'] 
                                : [];
                            
                            $marketPriceEur = $cardmarketPrices['lowest_near_mint'] ?? 0;
                            $priceDE = $cardmarketPrices['lowest_near_mint_DE'] ?? null;
                            $priceES = $cardmarketPrices['lowest_near_mint_ES'] ?? null;
                            $priceFR = $cardmarketPrices['lowest_near_mint_FR'] ?? null;
                            $priceIT = $cardmarketPrices['lowest_near_mint_IT'] ?? null;
                            $psaPrices = $cardmarketPrices['graded']['psa'] ?? [];
                            
                            // Convert to preferred currency if set
                            $marketPriceEurDisplay = $marketPriceEur;
                            if ($preferredCurrency && $marketPriceEur > 0) {
                                $marketPriceEurDisplay = \App\Services\CurrencyService::convert($marketPriceEur, 'EUR', $preferredCurrency);
                            }
                        @endphp
                        
                        @if($marketPriceEur > 0)
                            <!-- Main Cardmarket Price -->
                            <div class="mb-6">
                                <div class="border border-emerald-400/30 bg-emerald-500/20 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase mb-1">{{ __('tcg/cards/show.cardmarket_price') }} ({{ __('tcg/cards/show.near_mint') }})</div>
                                            @if($preferredCurrency)
                                                <div class="text-3xl font-bold text-white">
                                                    @php
                                                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                                        $formatted = number_format($marketPriceEurDisplay, 2);
                                                        if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                            echo "{$symbol}{$formatted}";
                                                        } else {
                                                            echo "{$formatted} {$symbol}";
                                                        }
                                                    @endphp
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">(€{{ number_format($marketPriceEur, 2) }})</div>
                                            @else
                                                <div class="text-3xl font-bold text-white">€{{ number_format($marketPriceEur, 2) }}</div>
                                            @endif
                                        </div>
                                        @if($cardmarketUrl)
                                            <a href="{{ $cardmarketUrl }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center gap-2">
                                                <span>{{ __('tcg/cards/show.buy_now') }}</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Regional Prices -->
                            @if($priceDE || $priceES || $priceFR || $priceIT)
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-300 mb-3">{{ __('tcg/cards/show.regional_prices') }} ({{ __('tcg/cards/show.near_mint') }})</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    @if($priceDE)
                                    <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                        <div class="text-xs text-gray-400 flex items-center gap-1">
                                            <span>🇩🇪</span>
                                            <span>Germany</span>
                                        </div>
                                        <div class="text-lg font-bold text-white">€{{ number_format($priceDE, 2) }}</div>
                                    </div>
                                    @endif
                                    @if($priceES)
                                    <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                        <div class="text-xs text-gray-400 flex items-center gap-1">
                                            <span>🇪🇸</span>
                                            <span>Spain</span>
                                        </div>
                                        <div class="text-lg font-bold text-white">€{{ number_format($priceES, 2) }}</div>
                                    </div>
                                    @endif
                                    @if($priceFR)
                                    <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                        <div class="text-xs text-gray-400 flex items-center gap-1">
                                            <span>🇫🇷</span>
                                            <span>France</span>
                                        </div>
                                        <div class="text-lg font-bold text-white">€{{ number_format($priceFR, 2) }}</div>
                                    </div>
                                    @endif
                                    @if($priceIT)
                                    <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                        <div class="text-xs text-gray-400 flex items-center gap-1">
                                            <span>🇮🇹</span>
                                            <span>Italy</span>
                                        </div>
                                        <div class="text-lg font-bold text-white">€{{ number_format($priceIT, 2) }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- PSA Graded Prices -->
                            @if(!empty($psaPrices))
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-300 mb-3">{{ __('tcg/cards/show.psa_graded_prices') }}</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($psaPrices as $grade => $price)
                                    <div class="border border-yellow-400/30 bg-yellow-500/20 rounded-lg p-3">
                                        <div class="text-xs text-gray-400 uppercase">{{ strtoupper($grade) }}</div>
                                        <div class="text-lg font-bold text-white">€{{ number_format($price, 2) }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <!-- Price Source Citation -->
                            <div class="text-xs text-gray-500 border-t border-white/10 pt-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p><strong>{{ __('tcg/cards/show.data_source') }}:</strong> Cardmarket via RapidAPI</p>
                                        @if($rapidapiCard && $rapidapiCard->updated_at)
                                        <p class="mt-1">{{ __('tcg/cards/show.last_updated') }}: {{ $rapidapiCard->updated_at->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>{{ __('tcg/cards/show.no_eu_prices') }}</p>
                            </div>
                        @endif
                    </div>
                    @else
                    <!-- Prices Hidden - Upgrade Required -->
                    <div class="py-8 px-6 text-center">
                        <div class="mb-4">
                            <svg class="mx-auto h-16 w-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">{{ __('prices.hidden.title') }}</h3>
                        <p class="text-gray-400 mb-6 max-w-md mx-auto">{{ __('prices.hidden.body') }}</p>
                        
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="{{ route('billing.index') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                {{ __('prices.hidden.cta_upgrade') }}
                            </a>
                            <span class="text-gray-500 self-center">{{ __('prices.hidden.or') }}</span>
                            <a href="{{ route('deck-evaluation.packages.index') }}" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-semibold flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                {{ __('prices.hidden.cta_deck_evaluation') }}
                            </a>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
