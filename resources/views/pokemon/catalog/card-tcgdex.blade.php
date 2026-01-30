@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <div class="mb-4">
            <a href="{{ route('pokemon.set.cards', $card->set->tcgdex_id) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to {{ $card->set->name_en ?? 'Set' }}
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
                    <h2 class="text-xl font-bold text-white mb-4">Card Details</h2>
                    
                    <dl class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Set</dt>
                            <dd class="text-sm text-white">
                                <a href="{{ route('pokemon.set.cards', $card->set->tcgdex_id) }}" class="text-blue-400 hover:underline">
                                    {{ $card->set->name_en ?? $card->set->tcgdex_id }}
                                </a>
                            </dd>
                        </div>
                        
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Number</dt>
                            <dd class="text-sm text-white">{{ $card->number ?? $card->local_id }}</dd>
                        </div>
                        
                        @if($card->supertype)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Card Type</dt>
                                <dd class="text-sm text-white">{{ $card->supertype }}</dd>
                            </div>
                        @endif
                        
                        @if($card->types)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Types</dt>
                                <dd class="text-sm text-white">{{ implode(', ', $card->types) }}</dd>
                            </div>
                        @endif
                        
                        @if($card->subtypes)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Subtypes</dt>
                                <dd class="text-sm text-white">{{ implode(', ', $card->subtypes) }}</dd>
                            </div>
                        @endif
                        
                        @if($card->hp)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">HP</dt>
                                <dd class="text-sm text-white">{{ $card->hp }}</dd>
                            </div>
                        @endif
                        
                        @if($card->rarity)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Rarity</dt>
                                <dd class="text-sm text-white">{{ $card->rarity }}</dd>
                            </div>
                        @endif
                        
                        @if($card->illustrator)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Artist</dt>
                                <dd class="text-sm text-white">{{ $card->illustrator }}</dd>
                            </div>
                        @endif
                        
                        @if($card->evolves_from)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Evolves from</dt>
                                <dd class="text-sm text-white">{{ $card->evolves_from }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['stage']))
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Stage</dt>
                                <dd class="text-sm text-white">{{ $card->raw['stage'] }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['dexId']) && count($card->raw['dexId']) > 0)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Pokédex #</dt>
                                <dd class="text-sm text-white">{{ implode(', ', $card->raw['dexId']) }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['retreat'])) 
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Retreat Cost</dt>
                                <dd class="text-sm text-white">{{ $card->raw['retreat'] }}</dd>
                            </div>
                        @endif
                        
                        @if(isset($card->raw['regulationMark']))
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Regulation Mark</dt>
                                <dd class="text-sm text-white font-bold">{{ $card->raw['regulationMark'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                
                @if(isset($card->raw['description']))
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Description</h2>
                        <p class="text-gray-300 text-sm italic">{{ $card->raw['description'] }}</p>
                    </div>
                @endif
                
                @if(isset($card->raw['attacks']) && count($card->raw['attacks']) > 0)
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Attacks</h2>
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
                        <h2 class="text-xl font-bold text-white mb-4">Weaknesses & Resistances</h2>
                        <div class="space-y-2">
                            @foreach($card->raw['weaknesses'] as $weakness)
                                <div class="flex justify-between items-center py-2 px-3 bg-red-900/20 border border-red-700/30 rounded">
                                    <span class="text-sm text-gray-300">Weakness: {{ $weakness['type'] }}</span>
                                    <span class="text-sm font-bold text-red-400">{{ $weakness['value'] }}</span>
                                </div>
                            @endforeach
                            @if(isset($card->raw['resistances']) && count($card->raw['resistances']) > 0)
                                @foreach($card->raw['resistances'] as $resistance)
                                    <div class="flex justify-between items-center py-2 px-3 bg-green-900/20 border border-green-700/30 rounded">
                                        <span class="text-sm text-gray-300">Resistance: {{ $resistance['type'] }}</span>
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
                                Regulation {{ $card->raw['regulationMark'] }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Artist Badge -->
                    @if($card->illustrator)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-yellow-600/20 border border-yellow-500/30 rounded-full">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            <span class="text-sm text-yellow-300 font-medium">{{ $card->illustrator }}</span>
                        </div>
                    @endif
                    
                    <!-- Legal & Variants Info -->
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <div class="flex flex-wrap gap-2 text-xs">
                            @if(isset($card->raw['legal']['standard']) && $card->raw['legal']['standard'])
                                <span class="inline-flex items-center gap-1 text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Standard
                                </span>
                            @endif
                            @if(isset($card->raw['legal']['expanded']) && $card->raw['legal']['expanded'])
                                <span class="inline-flex items-center gap-1 text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Expanded
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
                            <button class="flex items-center justify-center gap-2 px-4 py-3 bg-slate-600/50 hover:bg-slate-600/70 border border-slate-500/30 text-white font-medium rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="text-sm">Like</span>
                            </button>
                            
                            <button class="flex items-center justify-center gap-2 px-4 py-3 bg-slate-600/50 hover:bg-slate-600/70 border border-slate-500/30 text-white font-medium rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <span class="text-sm">Wishlist</span>
                            </button>
                            
                            <button class="flex items-center justify-center gap-2 px-4 py-3 bg-slate-600/50 hover:bg-slate-600/70 border border-slate-500/30 text-white font-medium rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span class="text-sm">Watch</span>
                            </button>
                        </div>
                        
                        <!-- Add to Collection and Deck -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            <button class="flex items-center justify-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Tilføj til Samling</span>
                            </button>
                            
                            <button class="flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span>Tilføj til Deck</span>
                            </button>
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
                        Market Prices
                    </h2>
                    
                    <div class="space-y-4">
                        @if($card->price_eur)
                            <div class="bg-[#1a1a19] border border-white/10 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-400">Cardmarket (EUR)</span>
                                    <span class="text-2xl font-bold text-green-400">
                                        €{{ number_format($card->price_eur, 2) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">Last updated: {{ $card->updated_at->diffForHumans() }}</p>
                            </div>
                        @endif
                        
                        @if($card->price_usd)
                            <div class="bg-[#1a1a19] border border-white/10 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-400">TCGPlayer (USD)</span>
                                    <span class="text-2xl font-bold text-blue-400">
                                        ${{ number_format($card->price_usd, 2) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">Last updated: {{ $card->updated_at->diffForHumans() }}</p>
                            </div>
                        @endif
                        
                        @if(!$card->price_eur && !$card->price_usd)
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">No pricing data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
