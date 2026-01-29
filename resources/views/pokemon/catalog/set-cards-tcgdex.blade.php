@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Link -->
            <div class="mb-4">
                <a href="{{ route('pokemon.sets') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Back to Sets
                </a>
            </div>

            <!-- Set Info -->
            <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden mb-6">
                <div class="p-6 text-gray-100">
                    <div class="flex items-start gap-6">
                        @if($set->logo_url)
                            <img src="{{ $set->logo_url }}.webp" 
                                 alt="{{ $set->name_en }}"
                                 class="w-48 h-32 object-contain">
                        @endif
                        <div>
                            <h3 class="text-2xl font-bold mb-2 text-white">{{ $set->name_en }}</h3>
                            <p class="text-gray-400">Series: {{ $set->series_name['en'] ?? 'N/A' }}</p>
                            <p class="text-gray-400">Total Cards: {{ $set->card_count_total ?? 0 }}</p>
                            @if($set->released_at)
                                <p class="text-gray-400">Released: {{ $set->released_at->format('F j, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6 text-gray-100">
                    @if($cards->isEmpty())
                        <p class="text-gray-500">No cards found in this set.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($cards as $card)
                                <a href="{{ route('pokemon.card', $card->tcgdex_id) }}" 
                                   class="block bg-[#1a1a19] border border-white/20 rounded-lg p-3 hover:shadow-xl hover:border-white/40 transition-all">
                                    
                                    @if($card->image_small_url)
                                        <img src="{{ $card->image_small_url }}/high.webp" 
                                             alt="{{ $card->name_en ?? $card->tcgdex_id }}"
                                             class="w-full rounded mb-2"
                                             loading="lazy">
                                    @endif
                                    
                                    <div class="text-sm">
                                        <p class="font-semibold truncate text-white">{{ $card->name_en ?? $card->tcgdex_id }}</p>
                                        <p class="text-gray-400 text-xs">{{ $card->number ?? $card->local_id }}</p>
                                        
                                        @if($card->rarity)
                                            <p class="text-xs text-gray-400 mt-1">{{ $card->rarity }}</p>
                                        @endif
                                        
                                        @if($card->price_eur)
                                            <p class="text-sm font-semibold text-green-400 mt-2">
                                                €{{ number_format($card->price_eur, 2) }}
                                            </p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            {{ $cards->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ $set->name_en }}
        <span class="text-sm text-gray-500">(TCGDEX)</span>
    </h2>
@endpush
