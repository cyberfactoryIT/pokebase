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
                        @if($imageUrl)
                            <img 
                                src="{{ $imageUrl }}" 
                                alt="{{ $card->name }}"
                                class="w-full h-full object-contain rounded-lg shadow-lg"
                                onerror="this.src='https://via.placeholder.com/490x684/1a1a19/666?text=No+Image'"
                            >
                        @else
                            <div class="w-full h-full bg-black/50 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Additional Details -->
                @if($card->raw && is_array($card->raw))
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('tcg/cards/show.additional_details') }}</h2>
                        
                        <dl class="space-y-3">
                            @foreach($card->raw as $key => $value)
                                @if(!is_array($value) && !is_object($value) && !in_array($key, ['raw', 'extended_data', 'extendedData', 'imageUrl', 'image_url', 'productId', 'categoryId', 'imageCount', 'modifiedOn']))
                                    <div class="flex justify-between py-2 border-b border-white/10">
                                        <dt class="text-sm font-medium text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                                        <dd class="text-sm text-white">
                                            @if(is_bool($value))
                                                {{ $value ? __('tcg/cards/show.yes') : __('tcg/cards/show.no') }}
                                            @elseif(filter_var($value, FILTER_VALIDATE_URL))
                                                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-blue-400 hover:text-blue-300 transition">
                                                    <span><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                </a>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
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
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ $card->group->name }}
                            </a>
                        @endif
                        
                        @if($card->card_number)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/10 border border-white/20 text-gray-200">
                                #{{ $card->card_number }}
                            </span>
                        @endif
                        
                        @if($card->rarity)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 border border-purple-400/30 text-purple-300">
                                {{ $card->rarity }}
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
                                        <a href="{{ route('decks.create') }}" class="block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-center">
                                            {{ __('tcg/cards/show.create_first_deck') }}
                                        </a>
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

                <!-- Pricing Section -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6" x-data="{ activeTab: localStorage.getItem('priceTab') || 'us' }">
                    <!-- Price Toggle Tabs -->
                    <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-2">
                        <h2 class="text-xl font-bold text-white">{{ __('tcg/cards/show.pricing') }}</h2>
                        
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
                    </div>
                    
                    <!-- US Prices (TCGCSV) -->
                    <div x-show="activeTab === 'us'" x-transition>
                    
                    @if($latestPrice)
                        <div class="grid grid-cols-2 gap-4">
                            @if($latestPrice->market_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.market_price') }}</div>
                                    <div class="text-2xl font-bold text-white">${{ number_format($latestPrice->market_price, 2) }}</div>
                                </div>
                            @endif
                            
                            @if($latestPrice->low_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.low_price') }}</div>
                                    <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->low_price, 2) }}</div>
                                </div>
                            @endif
                            
                            @if($latestPrice->mid_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.mid_price') }}</div>
                                    <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->mid_price, 2) }}</div>
                                </div>
                            @endif
                            
                            @if($latestPrice->high_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('tcg/cards/show.high_price') }}</div>
                                    <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->high_price, 2) }}</div>
                                </div>
                            @endif
                        </div>
                        
                        @if($latestPrice->printing)
                            <div class="mt-3 text-sm text-gray-300">
                                <span class="font-medium">{{ __('tcg/cards/show.printing') }}:</span> {{ $latestPrice->printing }}
                            </div>
                        @endif
                        
                        <div class="mt-3 text-xs text-gray-400">
                            {{ __('tcg/cards/show.last_updated') }}: {{ $latestPrice->snapshot_at->diffForHumans() }}
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
                    
                    <!-- EU Prices (Cardmarket) -->
                    <div x-show="activeTab === 'eu'" x-transition x-cloak>
                        @if($card->hasCardmarketVariants())
                            <x-cardmarket-variants :product="$card" />
                        @else
                            <div class="text-center py-8 text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>{{ __('tcg/cards/show.no_eu_prices') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Extended Data -->
                @if($card->extended_data && is_array($card->extended_data) && count($card->extended_data) > 0)
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('tcg/cards/show.extended_information') }}</h2>
                        
                        <dl class="space-y-3">
                            @foreach($card->extended_data as $item)
                                @if(isset($item['name']) && isset($item['value']))
                                    <div class="flex justify-between py-2 border-b border-white/10">
                                        <dt class="text-sm font-medium text-gray-400">{{ $item['name'] }}</dt>
                                        <dd class="text-sm text-white">{!! nl2br(strip_tags($item['value'], '<br>')) !!}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
