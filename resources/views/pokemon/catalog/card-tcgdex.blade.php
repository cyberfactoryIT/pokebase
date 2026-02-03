@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Navigation Buttons -->
        <div class="mb-4 flex items-center gap-4">
            <!-- Back Button (browser history) -->
            <button onclick="window.history.back()" class="inline-flex items-center text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('catalog.back') }}
            </button>
            
            <!-- Separator -->
            <span class="text-gray-600">|</span>
            
            <!-- View Set Button -->
            <a href="{{ route('pokemon.set.cards', $card->set->tcgdex_id) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300 transition">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                {{ __('catalog.view_set', ['set' => $card->set->name_en ?? __('catalog.set')]) }}
            </a>
        </div>

        <!-- Card Detail Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Card Image -->
            <div class="space-y-6">
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <div class="aspect-[245/342] max-w-md mx-auto">
                        @if($card->image_large_url)
                            <img src="{{ $card->image_large_url }}/high.webp" 
                                 alt="{{ $card->name_en ?? $card->tcgdex_id }}"
                                 class="w-full h-full object-contain rounded-lg shadow-lg">
                        @else
                            <div class="w-full h-full bg-black/50 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Details -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">{{ __('catalog.card_details') }}</h2>
                    
                    <dl class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">{{ __('catalog.set') }}</dt>
                            <dd class="text-sm text-white">
                                <a href="{{ route('pokemon.set.cards', $card->set->tcgdex_id) }}" class="text-blue-400 hover:underline">
                                    {{ $card->set->name_en ?? $card->set->tcgdex_id }}
                                </a>
                            </dd>
                        </div>
                        
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">{{ __('catalog.number') }}</dt>
                            <dd class="text-sm text-white">{{ $card->number ?? $card->local_id }}</dd>
                        </div>
                        
                        @if($card->supertype)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.card_type') }}</dt>
                                <dd class="text-sm text-white">{{ $card->supertype }}</dd>
                            </div>
                        @endif
                        
                        @if($card->types)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.types') }}</dt>
                                <dd class="text-sm text-white">{{ implode(', ', $card->types) }}</dd>
                            </div>
                        @endif
                        
                        @if($card->subtypes)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.subtypes') }}</dt>
                                <dd class="text-sm text-white">{{ implode(', ', $card->subtypes) }}</dd>
                            </div>
                        @endif
                        
                        @if($card->hp)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.hp') }}</dt>
                                <dd class="text-sm text-white">{{ $card->hp }}</dd>
                            </div>
                        @endif
                        
                        @if($card->rarity)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.rarity') }}</dt>
                                <dd class="text-sm text-white">{{ $card->rarity }}</dd>
                            </div>
                        @endif
                        
                        @if($card->illustrator)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.artist') }}</dt>
                                <dd class="text-sm text-white">{{ $card->illustrator }}</dd>
                            </div>
                        @endif
                        
                        @if($card->evolves_from)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.evolves_from') }}</dt>
                                <dd class="text-sm text-white">{{ $card->evolves_from }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['stage']))
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.stage') }}</dt>
                                <dd class="text-sm text-white">{{ $card->raw['stage'] }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['dexId']) && count($card->raw['dexId']) > 0)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.pokedex_number') }}</dt>
                                <dd class="text-sm text-white">{{ implode(', ', $card->raw['dexId']) }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['retreat'])) 
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.retreat_cost') }}</dt>
                                <dd class="text-sm text-white">{{ $card->raw['retreat'] }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['regulationMark']))
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">{{ __('catalog.regulation_mark') }}</dt>
                                <dd class="text-sm text-white font-bold">{{ $card->raw['regulationMark'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                
                @if(isset($card->raw['description']))
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('catalog.description') }}</h2>
                        <p class="text-gray-300 text-sm italic">{{ $card->raw['description'] }}</p>
                    </div>
                @endif
                
                @if(isset($card->raw['attacks']) && count($card->raw['attacks']) > 0)
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('catalog.attacks') }}</h2>
                        <div class="space-y-4">
                            @foreach($card->raw['attacks'] as $attack)
                                <div class="bg-[#1a1a19] border border-white/10 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="text-white font-semibold">{{ $attack['name'] }}</h3>
                                            @if(isset($attack['cost']) && count($attack['cost']) > 0)
                                                <div class="flex gap-1 mt-1">
                                                    @foreach($attack['cost'] as $cost)
                                                        <span class="text-xs px-2 py-0.5 bg-blue-600/30 text-blue-300 rounded">{{ $cost }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @if(isset($attack['damage']))
                                            <span class="text-2xl font-bold text-red-400">{{ $attack['damage'] }}</span>
                                        @endif
                                    </div>
                                    @if(isset($attack['effect']))
                                        <p class="text-sm text-gray-400 mt-2">{{ $attack['effect'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if(isset($card->raw['weaknesses']) && count($card->raw['weaknesses']) > 0)
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('catalog.weaknesses_resistances') }}</h2>
                        <div class="space-y-2">
                            @foreach($card->raw['weaknesses'] as $weakness)
                                <div class="flex justify-between items-center py-2 px-3 bg-red-900/20 border border-red-700/30 rounded">
                                    <span class="text-sm text-gray-300">{{ __('catalog.weakness') }}: {{ $weakness['type'] }}</span>
                                    <span class="text-sm font-bold text-red-400">{{ $weakness['value'] }}</span>
                                </div>
                            @endforeach
                            @if(isset($card->raw['resistances']) && count($card->raw['resistances']) > 0)
                                @foreach($card->raw['resistances'] as $resistance)
                                    <div class="flex justify-between items-center py-2 px-3 bg-green-900/20 border border-green-700/30 rounded">
                                        <span class="text-sm text-gray-300">{{ __('catalog.resistance') }}: {{ $resistance['type'] }}</span>
                                        <span class="text-sm font-bold text-green-400">{{ $resistance['value'] }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Prices & Actions -->
            <div class="space-y-6">
                <!-- Card Title & Badges -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h1 class="text-4xl font-bold text-white mb-4">{{ $card->name_en ?? $card->tcgdex_id }}</h1>
                    
                    <!-- Badges Row -->
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        @if($card->set)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 border border-white/20 text-gray-200 rounded-full text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/>
                                </svg>
                                {{ $card->set->name_en }}
                            </span>
                        @endif
                        
                        @if($card->number ?? $card->local_id)
                            <span class="px-3 py-1.5 bg-white/10 border border-white/20 text-gray-200 rounded-full text-sm">
                                #{{ $card->number ?? $card->local_id }}
                            </span>
                        @endif
                        
                        @if($card->hp)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500/20 border border-red-400/30 text-red-300 rounded-full text-sm font-semibold">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $card->hp }} HP
                            </span>
                        @endif
                        
                        @if($card->supertype)
                            <span class="px-3 py-1.5 bg-blue-500/20 border border-blue-400/30 text-blue-300 rounded-full text-sm">
                                {{ $card->supertype }}
                            </span>
                        @endif
                        
                        @if($card->rarity)
                            <span class="px-3 py-1.5 bg-purple-500/20 border border-purple-400/30 text-purple-300 rounded-full text-sm">
                                {{ $card->rarity }}
                            </span>
                        @endif
                        
                        @if(isset($card->raw['stage']))
                            <span class="px-3 py-1.5 bg-yellow-500/20 border border-yellow-400/30 text-yellow-300 rounded-full text-sm">
                                {{ $card->raw['stage'] }}
                            </span>
                        @endif
                        
                        @if(isset($card->raw['regulationMark']))
                            <span class="px-3 py-1.5 bg-white/10 border border-white/20 text-white rounded-full text-sm font-bold">
                                {{ __('catalog.regulation') }} {{ $card->raw['regulationMark'] }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Artist Badge -->
                    @if($card->illustrator)
                        <div class="inline-flex items-center px-3 py-1.5 bg-yellow-600/20 border border-yellow-500/30 rounded-full">
                            <span class="text-sm text-yellow-300 font-medium">{{ $card->illustrator }}</span>
                        </div>
                    @endif
                    
                    <!-- Legal & Variants Info -->
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <div class="flex flex-wrap gap-2 text-xs">
                            @if(isset($card->raw['legal']['standard']) && $card->raw['legal']['standard'])
                                <span class="inline-flex items-center gap-1 text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    {{ __('catalog.standard') }}
                                </span>
                            @endif
                            @if(isset($card->raw['legal']['expanded']) && $card->raw['legal']['expanded'])
                                <span class="inline-flex items-center gap-1 text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    {{ __('catalog.expanded') }}
                                </span>
                            @endif
                            @if(isset($card->raw['variants']))
                                @foreach($card->raw['variants'] as $variantType => $available)
                                    @if($available)
                                        <span class="text-gray-400">• {{ ucfirst(str_replace('_', ' ', $variantType)) }}</span>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    
                    @auth
                    <!-- Action Buttons -->
                    <div class="mt-6 pt-6 border-t border-white/10">
                        <!-- Like, Wishlist, Watch Row -->
                        <div class="grid grid-cols-3 gap-3 mb-3">
                            <button 
                                onclick="toggleLike('{{ $card->tcgdex_id }}', this)" 
                                class="flex items-center justify-center gap-2 px-4 py-3 {{ $isLiked ? 'bg-red-600 hover:bg-red-700' : 'bg-slate-600/50 hover:bg-slate-600/70' }} border border-slate-500/30 text-white font-medium rounded-lg transition"
                            >
                                <svg class="w-5 h-5" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="text-sm">{{ $isLiked ? __('catalog.unlike') : __('catalog.like') }}</span>
                            </button>
                            
                            <button 
                                onclick="toggleWishlist('{{ $card->tcgdex_id }}', this)" 
                                class="flex items-center justify-center gap-2 px-4 py-3 {{ $isWishlisted ? 'bg-purple-600 hover:bg-purple-700' : 'bg-slate-600/50 hover:bg-slate-600/70' }} border border-slate-500/30 text-white font-medium rounded-lg transition"
                            >
                                <svg class="w-5 h-5" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <span class="text-sm">{{ $isWishlisted ? __('catalog.in_wishlist') : __('catalog.wishlist') }}</span>
                            </button>
                            
                            <button 
                                onclick="toggleWatch('{{ $card->tcgdex_id }}', this)" 
                                class="flex items-center justify-center gap-2 px-4 py-3 {{ $isWatched ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-slate-600/50 hover:bg-slate-600/70' }} border border-slate-500/30 text-white font-medium rounded-lg transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span class="text-sm">{{ $isWatched ? __('catalog.watching') : __('catalog.watch') }}</span>
                            </button>
                        </div>
                        
                        <!-- Add to Collection and Deck -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3" x-data="{ showDeckModal: false }">
                            <form method="POST" action="{{ route('collection.add.tcgdex') }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="tcgdex_card_id" value="{{ $card->id }}">
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>{{ __('catalog.add_to_collection') }}</span>
                                </button>
                            </form>
                            
                            <button type="button" @click="showDeckModal = true" class="flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span>{{ __('catalog.add_to_deck') }}</span>
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
                                            $userDecks = Auth::check() ? Auth::user()->decks : collect();
                                        @endphp

                                        @if($userDecks->isEmpty())
                                            <p class="text-gray-400 mb-4">{{ __('tcg/cards/show.no_decks_yet') }}</p>
                                            @if(Auth::check() && Auth::user()->canCreateAnotherDeck())
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
                                                    <form method="POST" action="{{ route('decks.cards.add.tcgdex', $deck) }}">
                                                        @csrf
                                                        <input type="hidden" name="tcgdex_card_id" value="{{ $card->id }}">
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
                    @endauth
                </div>
                
                <!-- Market Prices -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('catalog.market_prices') }}
                    </h2>
                    
                    <div class="space-y-4">
                        @php
                            $pricing = $card->raw['pricing'] ?? null;
                            $cardmarket = $pricing['cardmarket'] ?? null;
                            $tcgplayer = $pricing['tcgplayer'] ?? null;
                            
                            // Get user's preferred currency
                            $user = auth()->user();
                            $preferredCurrency = $user?->preferred_currency;
                            
                            // Helper function to format EUR price with conversion
                            $formatEurPrice = function($eurAmount) use ($preferredCurrency) {
                                if (!$eurAmount) return null;
                                
                                $targetCurrency = $preferredCurrency && $preferredCurrency !== 'EUR' ? $preferredCurrency : 'EUR';
                                $symbolAfter = in_array($targetCurrency, ['DKK', 'SEK', 'NOK']);
                                $symbol = \App\Services\CurrencyService::getSymbol($targetCurrency);
                                
                                if ($preferredCurrency && $preferredCurrency !== 'EUR') {
                                    $converted = \App\Services\CurrencyService::convert($eurAmount, 'EUR', $preferredCurrency);
                                    $display = number_format($converted, 2);
                                    return [
                                        'display' => $display,
                                        'original' => number_format($eurAmount, 2),
                                        'symbol' => $symbol,
                                        'symbolAfter' => $symbolAfter,
                                        'formatted' => $symbolAfter ? "{$display} {$symbol}" : "{$symbol}{$display}"
                                    ];
                                }
                                
                                return [
                                    'display' => number_format($eurAmount, 2),
                                    'original' => null,
                                    'symbol' => '€',
                                    'symbolAfter' => false,
                                    'formatted' => '€' . number_format($eurAmount, 2)
                                ];
                            };
                            
                            // Helper function to format USD price with conversion
                            $formatUsdPrice = function($usdAmount) use ($preferredCurrency) {
                                if (!$usdAmount) return null;
                                
                                $targetCurrency = $preferredCurrency && $preferredCurrency !== 'USD' ? $preferredCurrency : 'USD';
                                $symbolAfter = in_array($targetCurrency, ['DKK', 'SEK', 'NOK']);
                                $symbol = \App\Services\CurrencyService::getSymbol($targetCurrency);
                                
                                if ($preferredCurrency && $preferredCurrency !== 'USD') {
                                    $converted = \App\Services\CurrencyService::convert($usdAmount, 'USD', $preferredCurrency);
                                    $display = number_format($converted, 2);
                                    return [
                                        'display' => $display,
                                        'original' => number_format($usdAmount, 2),
                                        'symbol' => $symbol,
                                        'symbolAfter' => $symbolAfter,
                                        'formatted' => $symbolAfter ? "{$display} {$symbol}" : "{$symbol}{$display}"
                                    ];
                                }
                                
                                return [
                                    'display' => number_format($usdAmount, 2),
                                    'original' => null,
                                    'symbol' => '$',
                                    'symbolAfter' => false,
                                    'formatted' => '$' . number_format($usdAmount, 2)
                                ];
                            };
                        @endphp
                        
                        {{-- Cardmarket Prices --}}
                        @if($cardmarket)
                            <div class="bg-[#1a1a19] border border-emerald-500/20 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                        </svg>
                                        <h3 class="font-semibold text-white">{{ __('catalog.cardmarket') }}</h3>
                                    </div>
                                    @if(isset($cardmarket['updated']))
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($cardmarket['updated'])->diffForHumans() }}</span>
                                    @endif
                                </div>
                                
                                {{-- Buy on Cardmarket Button --}}
                                @if(isset($cardmarketUrl) && $cardmarketUrl)
                                    <div class="mb-4">
                                        <a href="{{ $cardmarketUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-semibold rounded-lg transition shadow-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span>{{ __('catalog.buy_on_cardmarket') }}</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                                
                                {{-- Price History Chart --}}
                                @if(isset($cardmarket['idProduct']) && (!empty($priceHistory['trend']) || !empty($priceHistory['trend_holo'])))
                                    <div class="mb-4 border border-white/10 rounded-lg p-3 bg-black/20">
                                        <h4 class="text-xs font-semibold text-gray-400 mb-3 uppercase">{{ __('catalog.price_trend_30_days') }}</h4>
                                        <canvas id="cardmarketPriceChart" height="80"></canvas>
                                    </div>
                                @endif
                                
                                {{-- Main Trend Price --}}
                                @if(isset($cardmarket['trend']))
                                    @php $price = $formatEurPrice($cardmarket['trend']); @endphp
                                    @if($price)
                                        <div class="mb-4 pb-4 border-b border-white/10">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-400">{{ __('catalog.trend_price') }}</span>
                                                <div class="text-right">
                                                    <span class="text-3xl font-bold text-emerald-400">
                                                        {{ $price['formatted'] }}
                                                    </span>
                                                    @if($price['original'])
                                                        <div class="text-xs text-gray-500 mt-1">(€{{ $price['original'] }})</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                
                                {{-- Price Details Grid --}}
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    @if(isset($cardmarket['avg']))
                                        @php $price = $formatEurPrice($cardmarket['avg']); @endphp
                                        @if($price)
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">{{ __('catalog.average') }}:</span>
                                                <div class="text-right">
                                                    <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                    @if($price['original'])
                                                        <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    @if(isset($cardmarket['low']))
                                        @php $price = $formatEurPrice($cardmarket['low']); @endphp
                                        @if($price)
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">{{ __('catalog.low') }}:</span>
                                                <div class="text-right">
                                                    <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                    @if($price['original'])
                                                        <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    @if(isset($cardmarket['avg1']))
                                        @php $price = $formatEurPrice($cardmarket['avg1']); @endphp
                                        @if($price)
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">{{ __('catalog.day_avg_1') }}:</span>
                                                <div class="text-right">
                                                    <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                    @if($price['original'])
                                                        <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    @if(isset($cardmarket['avg7']))
                                        @php $price = $formatEurPrice($cardmarket['avg7']); @endphp
                                        @if($price)
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">{{ __('catalog.day_avg_7') }}:</span>
                                                <div class="text-right">
                                                    <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                    @if($price['original'])
                                                        <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    @if(isset($cardmarket['avg30']))
                                        @php $price = $formatEurPrice($cardmarket['avg30']); @endphp
                                        @if($price)
                                            <div class="flex justify-between">
                                                <span class="text-gray-400">{{ __('catalog.day_avg_30') }}:</span>
                                                <div class="text-right">
                                                    <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                    @if($price['original'])
                                                        <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                
                                {{-- Holofoil Prices if available --}}
                                @if(isset($cardmarket['trend-holo']) || isset($cardmarket['avg1-holo']) || isset($cardmarket['avg7-holo']) || isset($cardmarket['avg30-holo']))
                                    <div class="mt-4 pt-4 border-t border-white/10">
                                        <h4 class="text-xs font-semibold text-gray-400 mb-2 uppercase">{{ __('catalog.holofoil_variant') }}</h4>
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            @if(isset($cardmarket['trend-holo']))
                                                @php $price = $formatEurPrice($cardmarket['trend-holo']); @endphp
                                                @if($price)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400">{{ __('catalog.trend') }}:</span>
                                                        <div class="text-right">
                                                            <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                            @if($price['original'])
                                                                <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            @if(isset($cardmarket['avg1-holo']))
                                                @php $price = $formatEurPrice($cardmarket['avg1-holo']); @endphp
                                                @if($price)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400">{{ __('catalog.day_1') }}:</span>
                                                        <div class="text-right">
                                                            <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                            @if($price['original'])
                                                                <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            @if(isset($cardmarket['avg7-holo']))
                                                @php $price = $formatEurPrice($cardmarket['avg7-holo']); @endphp
                                                @if($price)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400">{{ __('catalog.day_7') }}:</span>
                                                        <div class="text-right">
                                                            <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                            @if($price['original'])
                                                                <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            @if(isset($cardmarket['avg30-holo']))
                                                @php $price = $formatEurPrice($cardmarket['avg30-holo']); @endphp
                                                @if($price)
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400">{{ __('catalog.day_30') }}:</span>
                                                        <div class="text-right">
                                                            <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                            @if($price['original'])
                                                                <div class="text-xs text-gray-500">(€{{ $price['original'] }})</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        {{-- TCGPlayer Prices --}}
                        @if($tcgplayer)
                            <div class="bg-[#1a1a19] border border-blue-500/20 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                        </svg>
                                        <h3 class="font-semibold text-white">{{ __('catalog.tcgplayer') }}</h3>
                                    </div>
                                    @if(isset($tcgplayer['updated']))
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($tcgplayer['updated'])->diffForHumans() }}</span>
                                    @endif
                                </div>
                                
                                {{-- Variants --}}
                                @php
                                    $variants = collect($tcgplayer)->except(['unit', 'updated']);
                                @endphp
                                
                                @foreach($variants as $variantName => $variantData)
                                    @if(is_array($variantData))
                                        <div class="mb-4 last:mb-0">
                                            <h4 class="text-xs font-semibold text-gray-400 mb-2 uppercase">
                                                {{ str_replace('-', ' ', ucwords($variantName, '-')) }}
                                            </h4>
                                            
                                            {{-- Main Market Price --}}
                                            @if(isset($variantData['marketPrice']) && $variantData['marketPrice'])
                                                @php $price = $formatUsdPrice($variantData['marketPrice']); @endphp
                                                @if($price)
                                                    <div class="mb-3 pb-3 border-b border-white/10">
                                                        <div class="flex justify-between items-center">
                                                        <span class="text-sm text-gray-400">{{ __('catalog.market_price') }}</span>
                                                        <div class="text-right">
                                                            <span class="text-2xl font-bold text-blue-400">
                                                                {{ $price['formatted'] }}
                                                            </span>
                                                            @if($price['original'])
                                                                <div class="text-xs text-gray-500 mt-1">(${{ $price['original'] }})</div>
                                                            @endif
                                                        </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            
                                            <div class="grid grid-cols-2 gap-3 text-sm">
                                                @if(isset($variantData['lowPrice']) && $variantData['lowPrice'])
                                                    @php $price = $formatUsdPrice($variantData['lowPrice']); @endphp
                                                    @if($price)
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-400">{{ __('catalog.low') }}:</span>
                                                            <div class="text-right">
                                                                <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                                @if($price['original'])
                                                                    <div class="text-xs text-gray-500">(${{ $price['original'] }})</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                                @if(isset($variantData['midPrice']) && $variantData['midPrice'])
                                                    @php $price = $formatUsdPrice($variantData['midPrice']); @endphp
                                                    @if($price)
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-400">{{ __('catalog.mid') }}:</span>
                                                            <div class="text-right">
                                                                <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                                @if($price['original'])
                                                                    <div class="text-xs text-gray-500">(${{ $price['original'] }})</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                                @if(isset($variantData['highPrice']) && $variantData['highPrice'])
                                                    @php $price = $formatUsdPrice($variantData['highPrice']); @endphp
                                                    @if($price)
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-400">{{ __('catalog.high') }}:</span>
                                                            <div class="text-right">
                                                                <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                                @if($price['original'])
                                                                    <div class="text-xs text-gray-500">(${{ $price['original'] }})</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                                @if(isset($variantData['directLowPrice']) && $variantData['directLowPrice'])
                                                    @php $price = $formatUsdPrice($variantData['directLowPrice']); @endphp
                                                    @if($price)
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-400">{{ __('catalog.direct_low') }}:</span>
                                                            <div class="text-right">
                                                                <span class="text-white font-medium">{{ $price['formatted'] }}</span>
                                                                @if($price['original'])
                                                                    <div class="text-xs text-gray-500">(${{ $price['original'] }})</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        
                        {{-- No Pricing Data --}}
                        @if(!$cardmarket && !$tcgplayer)
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">{{ __('catalog.no_pricing_data') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js for Price History --}}
@if(isset($cardmarket['idProduct']) && (!empty($priceHistory['trend']) || !empty($priceHistory['trend_holo'])))
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1/dist/chartjs-adapter-luxon.umd.min.js"></script>
    <script>
    (function() {
        const ctx = document.getElementById('cardmarketPriceChart');
        if (!ctx) return;
        
        const trendData = @json($priceHistory['trend'] ?? []);
        const trendHoloData = @json($priceHistory['trend_holo'] ?? []);
        
        const datasets = [];
        
        if (trendData.length > 0) {
            datasets.push({
                label: 'Trend',
                data: trendData,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true
            });
        }
        
        if (trendHoloData.length > 0) {
            datasets.push({
                label: 'Trend Holo',
                data: trendHoloData,
                borderColor: 'rgb(168, 85, 247)',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true
            });
        }
        
        new Chart(ctx, {
            type: 'line',
            data: { datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'MMM d'
                            }
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.5)',
                            maxRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.5)',
                            callback: function(value) {
                                return '€' + value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: datasets.length > 1,
                        position: 'top',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            padding: 10,
                            font: {
                                size: 11
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: 'rgba(255, 255, 255, 0.9)',
                        bodyColor: 'rgba(255, 255, 255, 0.9)',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': €' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    })();
    </script>
@endif

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
            const span = button.querySelector('span');
            if (data.status === 'liked') {
                button.classList.remove('bg-slate-600/50', 'hover:bg-slate-600/70');
                button.classList.add('bg-red-600', 'hover:bg-red-700');
                svg.setAttribute('fill', 'currentColor');
                span.textContent = '{{ __('catalog.unlike') }}';
            } else {
                button.classList.remove('bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-slate-600/50', 'hover:bg-slate-600/70');
                svg.setAttribute('fill', 'none');
                span.textContent = '{{ __('catalog.like') }}';
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
            const span = button.querySelector('span');
            if (data.status === 'added') {
                button.classList.remove('bg-slate-600/50', 'hover:bg-slate-600/70');
                button.classList.add('bg-purple-600', 'hover:bg-purple-700');
                svg.setAttribute('fill', 'currentColor');
                span.textContent = '{{ __('catalog.in_wishlist') }}';
            } else {
                button.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                button.classList.add('bg-slate-600/50', 'hover:bg-slate-600/70');
                svg.setAttribute('fill', 'none');
                span.textContent = '{{ __('catalog.wishlist') }}';
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
            const svg = button.querySelector('svg');
            const span = button.querySelector('span');
            if (data.status === 'watched') {
                button.classList.remove('bg-slate-600/50', 'hover:bg-slate-600/70');
                button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
                span.textContent = '{{ __('catalog.watching') }}';
            } else {
                button.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
                button.classList.add('bg-slate-600/50', 'hover:bg-slate-600/70');
                span.textContent = '{{ __('catalog.watch') }}';
            }
        }
    } catch (error) {
        console.error('Error toggling watch:', error);
    }
}
</script>

@endsection
