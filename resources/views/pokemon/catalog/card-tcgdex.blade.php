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
                    </dl>
                </div>
            </div>

            <!-- Right Column: Prices & Actions -->
            <div class="space-y-6">
                <!-- Card Title & Basic Info -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $card->name_en ?? $card->tcgdex_id }}</h1>
                    @if($card->set)
                        <p class="text-gray-400 text-sm">{{ $card->set->name_en }} • {{ $card->number ?? $card->local_id }}</p>
                    @endif
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

                <!-- Actions -->
                @auth
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Actions</h2>
                    
                    <div class="space-y-3">
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add to Collection
                        </button>
                        
                        <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                            Add to Wishlist
                        </button>
                        
                        <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Watch Price
                        </button>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
