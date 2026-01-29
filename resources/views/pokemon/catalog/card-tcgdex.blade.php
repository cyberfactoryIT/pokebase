@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Link -->
            <div class="mb-4">
                <a href="{{ route('pokemon.set.cards', $card->set->tcgdex_id) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Back to {{ $card->set->name_en ?? 'Set' }}
                </a>
            </div>

            <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6 text-gray-100">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Card Image -->
                        <div>
                            @if($card->image_large_url)
                                <img src="{{ $card->image_large_url }}/high.webp" 
                                     alt="{{ $card->name_en ?? $card->tcgdex_id }}"
                                     class="w-full max-w-md mx-auto rounded-lg shadow-lg">
                            @endif
                        </div>
                        
                        <!-- Card Details -->
                        <div>
                            <h3 class="text-3xl font-bold mb-4 text-white">{{ $card->name_en ?? $card->tcgdex_id }}</h3>
                            
                            <div class="space-y-3 text-gray-300">
                                <div>
                                    <span class="font-semibold text-white">Set:</span>
                                    <a href="{{ route('pokemon.set.cards', $card->set->tcgdex_id) }}" class="text-blue-600 hover:underline">
                                        {{ $card->set->name_en ?? $card->set->tcgdex_id }}
                                    </a>
                                </div>
                                
                                <div>
                                    <span class="font-semibold">Number:</span> {{ $card->number ?? $card->local_id }}
                                </div>
                                
                                @if($card->supertype)
                                    <div>
                                        <span class="font-semibold text-white">Type:</span> {{ $card->supertype }}
                                    </div>
                                @endif
                                
                                @if($card->types)
                                    <div>
                                        <span class="font-semibold text-white">Types:</span> {{ implode(', ', $card->types) }}
                                    </div>
                                @endif
                                
                                @if($card->subtypes)
                                    <div>
                                        <span class="font-semibold text-white">Subtypes:</span> {{ implode(', ', $card->subtypes) }}
                                    </div>
                                @endif
                                
                                @if($card->hp)
                                    <div>
                                        <span class="font-semibold text-white">HP:</span> {{ $card->hp }}
                                    </div>
                                @endif
                                
                                @if($card->rarity)
                                    <div>
                                        <span class="font-semibold text-white">Rarity:</span> {{ $card->rarity }}
                                    </div>
                                @endif
                                
                                @if($card->illustrator)
                                    <div>
                                        <span class="font-semibold text-white">Illustrator:</span> {{ $card->illustrator }}
                                    </div>
                                @endif
                                
                                @if($card->evolves_from)
                                    <div>
                                        <span class="font-semibold text-white">Evolves from:</span> {{ $card->evolves_from }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Pricing -->
                            <div class="mt-6 p-4 bg-[#1a1a19] border border-white/20 rounded-lg">
                                <h4 class="font-bold text-lg mb-3 text-white">Market Prices</h4>
                                <div class="space-y-2">
                                    @if($card->price_eur)
                                        <div class="flex justify-between">
                                            <span>Cardmarket (EUR):</span>
                                            <span class="font-semibold text-green-600 dark:text-green-400">
                                                €{{ number_format($card->price_eur, 2) }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if($card->price_usd)
                                        <div class="flex justify-between">
                                            <span>TCGPlayer (USD):</span>
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                                ${{ number_format($card->price_usd, 2) }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if(!$card->price_eur && !$card->price_usd)
                                        <p class="text-gray-500 text-sm">No pricing data available</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Add to Collection Button -->
                            <div class="mt-6">
                                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                                    Add to Collection
                                </button>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
@endsection

@push('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ $card->name_en ?? $card->tcgdex_id }}
        <span class="text-sm text-gray-500">(TCGDEX)</span>
    </h2>
@endpush
