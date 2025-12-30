@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">{{ __('deckvaluation.step1_title') }}</h1>
            <p class="text-gray-400">{{ __('deckvaluation.step1_subtitle') }}</p>
        </div>

        @if(session('success'))
        <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4 mb-6">
            <p class="text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-900/30 border border-red-500/30 rounded-lg p-4 mb-6">
            <p class="text-red-200">{{ session('error') }}</p>
        </div>
        @endif

        <!-- Progress indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">1</div>
                    <div class="w-16 h-1 bg-blue-500"></div>
                    <div class="bg-gray-600 text-white rounded-full w-10 h-10 flex items-center justify-center">2</div>
                    <div class="w-16 h-1 bg-gray-600"></div>
                    <div class="bg-gray-600 text-white rounded-full w-10 h-10 flex items-center justify-center">3</div>
                </div>
            </div>
            <div class="flex justify-between max-w-md mx-auto mt-2">
                <span class="text-blue-400 font-semibold text-sm">{{ __('deckvaluation.progress_step1') }}</span>
                <span class="text-gray-500 text-sm">{{ __('deckvaluation.progress_step2') }}</span>
                <span class="text-gray-500 text-sm">{{ __('deckvaluation.progress_step3') }}</span>
            </div>
        </div>

        <!-- Entitlement Progress Indicator -->
        @if(isset($entitlement))
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl mb-6 p-6">
            @if($entitlement['type'] === 'free')
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white font-semibold mb-1">{{ __('deck_evaluation.entitlement.free.title') }}</h3>
                        <p class="text-gray-400 text-sm">
                            {{ __('deck_evaluation.entitlement.free.usage', [
                                'used' => $entitlement['cards_used'],
                                'limit' => $entitlement['cards_limit']
                            ]) }}
                        </p>
                    </div>
                    <div class="text-right">
                        @php
                            $percentUsed = $entitlement['cards_limit'] > 0 ? ($entitlement['cards_used'] / $entitlement['cards_limit']) * 100 : 0;
                            $badgeColor = $percentUsed >= 100 ? 'bg-red-500' : ($percentUsed >= 80 ? 'bg-yellow-500' : 'bg-blue-500');
                        @endphp
                        <div class="inline-flex items-center gap-2 {{ $badgeColor }} text-white px-4 py-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="font-bold">{{ $entitlement['cards_remaining'] }}</span>
                            <span class="text-sm">{{ __('deck_evaluation.progress.remaining') }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 bg-black/30 rounded-full h-2 overflow-hidden">
                    <div class="{{ $badgeColor }} h-full transition-all duration-300" style="width: {{ min($percentUsed, 100) }}%"></div>
                </div>
                @if($percentUsed >= 80)
                    <div class="mt-3 text-center">
                        <a href="{{ route('deck-evaluation.packages') }}" class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm font-medium">
                            {{ __('deck_evaluation.errors.free_limit_exceeded.cta') }} →
                        </a>
                    </div>
                @endif
            @else
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white font-semibold mb-1">
                            {{ __('deck_evaluation.entitlement.purchased.title', ['package' => $entitlement['package_name']]) }}
                        </h3>
                        @if($entitlement['is_unlimited'])
                            <p class="text-gray-400 text-sm">
                                {{ __('deck_evaluation.entitlement.purchased.usage_unlimited', ['used' => $entitlement['cards_used']]) }}
                            </p>
                        @else
                            <p class="text-gray-400 text-sm">
                                {{ __('deck_evaluation.entitlement.purchased.usage', [
                                    'used' => $entitlement['cards_used'],
                                    'limit' => $entitlement['cards_limit']
                                ]) }}
                            </p>
                        @endif
                        <p class="text-gray-500 text-xs mt-1">
                            {{ __('deck_evaluation.entitlement.purchased.expires', [
                                'date' => $entitlement['expires_at']->format('M d, Y')
                            ]) }}
                        </p>
                    </div>
                    <div class="text-right">
                        @if($entitlement['is_unlimited'])
                            <div class="inline-flex items-center gap-2 bg-purple-500 text-white px-4 py-2 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                                <span class="font-bold">{{ __('deck_evaluation.progress.unlimited') }}</span>
                            </div>
                        @else
                            @php
                                $percentUsed = $entitlement['cards_limit'] > 0 ? ($entitlement['cards_used'] / $entitlement['cards_limit']) * 100 : 0;
                                $badgeColor = $percentUsed >= 100 ? 'bg-red-500' : ($percentUsed >= 80 ? 'bg-yellow-500' : 'bg-green-500');
                            @endphp
                            <div class="inline-flex items-center gap-2 {{ $badgeColor }} text-white px-4 py-2 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-bold">{{ $entitlement['cards_remaining'] }}</span>
                                <span class="text-sm">{{ __('deck_evaluation.progress.remaining') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                @if(!$entitlement['is_unlimited'])
                    <div class="mt-3 bg-black/30 rounded-full h-2 overflow-hidden">
                        <div class="{{ $badgeColor }} h-full transition-all duration-300" style="width: {{ min($percentUsed, 100) }}%"></div>
                    </div>
                @endif
            @endif
        </div>
        @endif

        <!-- Search Card -->
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl mb-6 p-6">
            <h2 class="text-lg font-semibold text-white mb-4">{{ __('deckvaluation.step1_search_title') }}</h2>
            <div class="relative" x-data="{ searchOpen: false }" @click.away="searchOpen = false">
                <input 
                    type="text" 
                    id="valuation-card-search" 
                    placeholder="{{ __('deckvaluation.step1_search_placeholder') }}"
                    class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @focus="searchOpen = true"
                >
                <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <div id="valuation-search-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                    <!-- Results will be inserted here by JS -->
                </div>
            </div>
        </div>

        <!-- Current Deck -->
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl mb-6 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-white">{{ __('deckvaluation.step1_deck_title', ['count' => $itemsWithDetails->count()]) }}</h2>
                @if($itemsWithDetails->count() > 0)
                <a href="{{ route('pokemon.deck-valuation.step2') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    {{ __('deckvaluation.step1_continue') }} →
                </a>
                @endif
            </div>

            @if($itemsWithDetails->count() === 0)
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-gray-400">{{ __('deckvaluation.step1_no_cards') }}</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($itemsWithDetails as $item)
                <div class="bg-black/30 border border-white/10 rounded-lg p-4 flex items-center gap-4">
                    @if($item['card']->image_url)
                    <img src="{{ $item['card']->image_url }}" alt="{{ $item['card']->name }}" class="w-16 h-22 object-cover rounded">
                    @endif
                    <div class="flex-1">
                        <h3 class="text-white font-semibold">{{ $item['card']->name }}</h3>
                        <p class="text-gray-400 text-sm">{{ $item['card']->group?->name ?? 'Unknown Set' }} • {{ $item['card']->card_number }}</p>
                        <p class="text-gray-500 text-xs">Quantity: {{ $item['qty'] }}</p>
                    </div>
                    <form method="POST" action="{{ route('pokemon.deck-valuation.remove') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                        <button type="submit" class="text-red-400 hover:text-red-300 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('valuation-card-search');
    const dropdown = document.getElementById('valuation-search-dropdown');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            dropdown.classList.add('hidden');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('pokemon.deck-valuation.search') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        dropdown.innerHTML = '<div class="p-4 text-gray-400 text-center">No cards found</div>';
                    } else {
                        dropdown.innerHTML = data.map(card => `
                            <div class="border-b border-white/10 last:border-0">
                                <form method="POST" action="{{ route('pokemon.deck-valuation.add') }}" class="hover:bg-white/5 transition p-3">
                                    @csrf
                                    <input type="hidden" name="product_id" value="${card.product_id}">
                                    <button type="submit" class="w-full text-left flex items-center gap-3">
                                        ${card.image_url ? `<img src="${card.image_url}" class="w-12 h-16 object-cover rounded" alt="${card.name}">` : ''}
                                        <div class="flex-1">
                                            <div class="text-white font-medium">${card.name}</div>
                                            <div class="text-gray-400 text-sm">${card.group_name} • ${card.card_number}</div>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        `).join('');
                    }
                    dropdown.classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Search error:', err);
                    dropdown.innerHTML = '<div class="p-4 text-red-400 text-center">Error searching cards</div>';
                    dropdown.classList.remove('hidden');
                });
        }, 300);
    });
});
</script>
@endsection
