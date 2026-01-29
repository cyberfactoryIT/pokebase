<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('pokemon.sets') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Back to Sets
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mt-2">
                    {{ $set->name['en'] ?? $set->tcgdex_id }}
                    <span class="text-sm text-gray-500">(TCGDEX)</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Set Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-start gap-6">
                        @if($set->logo_url)
                            <img src="{{ $set->logo_url }}.webp" 
                                 alt="{{ $set->name['en'] ?? $set->tcgdex_id }}"
                                 class="w-48 h-32 object-contain">
                        @endif
                        <div>
                            <h3 class="text-2xl font-bold mb-2">{{ $set->name['en'] ?? $set->tcgdex_id }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">Series: {{ $set->series_name['en'] ?? 'N/A' }}</p>
                            <p class="text-gray-600 dark:text-gray-400">Total Cards: {{ $set->card_count_total ?? 0 }}</p>
                            @if($set->released_at)
                                <p class="text-gray-600 dark:text-gray-400">Released: {{ $set->released_at->format('F j, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($cards->isEmpty())
                        <p class="text-gray-500">No cards found in this set.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($cards as $card)
                                <a href="{{ route('pokemon.card', $card->tcgdex_id) }}" 
                                   class="block bg-gray-50 dark:bg-gray-700 rounded-lg p-3 hover:shadow-lg transition-shadow">
                                    
                                    @if($card->image_small_url)
                                        <img src="{{ $card->image_small_url }}" 
                                             alt="{{ $card->name['en'] ?? $card->tcgdex_id }}"
                                             class="w-full rounded mb-2"
                                             loading="lazy">
                                    @endif
                                    
                                    <div class="text-sm">
                                        <p class="font-semibold truncate">{{ $card->name['en'] ?? $card->tcgdex_id }}</p>
                                        <p class="text-gray-500 text-xs">{{ $card->number ?? $card->local_id }}</p>
                                        
                                        @if($card->rarity)
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $card->rarity }}</p>
                                        @endif
                                        
                                        @if($card->price_eur)
                                            <p class="text-sm font-semibold text-green-600 dark:text-green-400 mt-2">
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
</x-app-layout>
