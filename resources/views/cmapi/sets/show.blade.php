@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="/{{ $game }}/sets" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('catalog.back_to_sets') }}
            </a>
        </div>

        <!-- Set Header -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
            <div class="px-6 py-6 flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-white">{{ $set->name }}</h1>
                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-300">
                        <span class="px-3 py-1 bg-white/10 border border-white/20 rounded-full font-semibold">{{ __('catalog.episode_prefix') }}{{ $set->cmapi_episode }}</span>
                        @if($set->release_date)
                            <span>{{ __('catalog.released') }}: {{ $set->release_date->format('F j, Y') }}</span>
                        @endif
                        <span>{{ $set->cards_count }}{{ __('catalog.cards_in_db') }}</span>
                    </div>
                </div>
                @if($set->logo_url)
                    <div class="ml-6 flex-shrink-0">
                        <img 
                            src="{{ $set->logo_url }}" 
                            alt="{{ $set->name }} logo" 
                            class="h-20 w-auto object-contain"
                            onerror="this.style.display='none'"
                        >
                    </div>
                @endif
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
            <div class="px-6 py-4 space-y-4">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="{{ __('catalog.search_placeholder') }}" 
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                
                <!-- Filters -->
                <div class="flex gap-3">
                    <select id="rarityFilter" class="px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white text-sm">
                        <option value="">{{ __('catalog.all_rarities') }}</option>
                    </select>
                    @if($game === 'lorcana')
                    <select id="cardTypeFilter" class="px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white text-sm">
                        <option value="">{{ __('catalog.all_types') }}</option>
                        <option value="Character">{{ __('catalog.character') }}</option>
                        <option value="Action">{{ __('catalog.action') }}</option>
                        <option value="Item">{{ __('catalog.item') }}</option>
                        <option value="Location">{{ __('catalog.location') }}</option>
                    </select>
                    <select id="inkColorFilter" class="px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white text-sm">
                        <option value="">{{ __('catalog.all_ink_colors') }}</option>
                        <option value="Amber">{{ __('catalog.amber') }}</option>
                        <option value="Amethyst">{{ __('catalog.amethyst') }}</option>
                        <option value="Emerald">{{ __('catalog.emerald') }}</option>
                        <option value="Ruby">{{ __('catalog.ruby') }}</option>
                        <option value="Sapphire">{{ __('catalog.sapphire') }}</option>
                        <option value="Steel">{{ __('catalog.steel') }}</option>
                    </select>
                    @endif
                    <button onclick="clearFilters()" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-gray-300 rounded-lg text-sm transition">
                        {{ __('catalog.clear_filters') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8 text-center text-gray-400 hidden">
            <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('catalog.loading_cards') }}
        </div>

        <!-- Cards Grid -->
        <div id="cardsSection" class="mb-8 hidden">
            <div id="cardsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- No Results -->
        <div id="noResults" class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-12 text-center text-gray-400 hidden">
            <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg">{{ __('catalog.no_cards_found') }}</p>
        </div>

        <!-- Load More -->
        <div id="loadMoreContainer" class="text-center hidden">
            <button 
                id="loadMoreBtn"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-lg"
            >
                {{ __('catalog.load_more_cards') }}
            </button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let isLoading = false;
let searchQuery = '';
let rarityFilter = '';
let cardTypeFilter = '';
let inkColorFilter = '';
const game = '{{ $game }}';
const episodeId = {{ $set->cmapi_episode }};

// User preferences for price display
@php
    $user = auth()->user();
    $canSeePrices = $user && ($user->isAdvanced() || $user->isPremium());
    $preferredCurrency = $canSeePrices && $user ? ($user->preferred_currency ?? 'EUR') : 'EUR';
@endphp
const userCanSeePrices = {{ $canSeePrices ? 'true' : 'false' }};
const preferredCurrency = '{{ $preferredCurrency }}';
const exchangeRates = {
    'EUR': 1.0,
    'USD': 1.05,
    'GBP': 0.85,
    'DKK': 7.46,
    'SEK': 11.20,
    'NOK': 11.50,
    'CHF': 0.95,
    'JPY': 155.0,
    'CAD': 1.45,
    'AUD': 1.65,
};
const currencySymbols = {
    'EUR': '€',
    'USD': '$',
    'GBP': '£',
    'DKK': 'kr',
    'SEK': 'kr',
    'NOK': 'kr',
    'CHF': 'CHF',
    'JPY': '¥',
    'CAD': 'C$',
    'AUD': 'A$',
};

function convertPrice(amount, from, to) {
    if (from === to) return amount;
    const amountInEur = amount / exchangeRates[from];
    return amountInEur * exchangeRates[to];
}

function formatPrice(amount, currency) {
    const symbol = currencySymbols[currency] || currency;
    const formatted = amount.toFixed(2);
    
    if (['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'].includes(currency)) {
        return `${symbol}${formatted}`;
    }
    return `${formatted} ${symbol}`;
}

document.addEventListener('DOMContentLoaded', function() {
    loadCards(1);
    
    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = e.target.value;
            loadCards(1);
        }, 300);
    });
    
    // Filters
    document.getElementById('rarityFilter').addEventListener('change', function(e) {
        rarityFilter = e.target.value;
        loadCards(1);
    });
    
    @if($game === 'lorcana')
    document.getElementById('cardTypeFilter').addEventListener('change', function(e) {
        cardTypeFilter = e.target.value;
        loadCards(1);
    });
    
    document.getElementById('inkColorFilter').addEventListener('change', function(e) {
        inkColorFilter = e.target.value;
        loadCards(1);
    });
    @endif
    
    // Load More
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadCards(currentPage + 1, true);
        });
    }
});

function clearFilters() {
    searchQuery = '';
    rarityFilter = '';
    cardTypeFilter = '';
    inkColorFilter = '';
    document.getElementById('searchInput').value = '';
    document.getElementById('rarityFilter').value = '';
    @if($game === 'lorcana')
    document.getElementById('cardTypeFilter').value = '';
    document.getElementById('inkColorFilter').value = '';
    @endif
    loadCards(1);
}

function loadCards(page = 1, append = false) {
    if (isLoading) return;
    isLoading = true;
    
    const loadingState = document.getElementById('loadingState');
    const cardsSection = document.getElementById('cardsSection');
    const noResults = document.getElementById('noResults');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    
    if (!append) {
        loadingState.classList.remove('hidden');
        cardsSection.classList.add('hidden');
        noResults.classList.add('hidden');
    }
    loadMoreContainer.classList.add('hidden');
    
    const params = new URLSearchParams({
        page: page,
        query: searchQuery,
        rarity: rarityFilter,
        card_type: cardTypeFilter,
        ink_color: inkColorFilter
    });
    
    fetch(`/${game}/sets/${episodeId}/cards/search?${params}`)
        .then(response => response.json())
        .then(data => {
            currentPage = data.meta.current_page;
            
            if (!append) {
                document.getElementById('cardsGrid').innerHTML = '';
            }
            
            if (data.data.length === 0 && !append) {
                loadingState.classList.add('hidden');
                noResults.classList.remove('hidden');
            } else {
                loadingState.classList.add('hidden');
                cardsSection.classList.remove('hidden');
                
                // Populate rarity filter on first load
                if (page === 1 && !append) {
                    populateRarityFilter(data.data);
                }
                
                data.data.forEach(card => {
                    document.getElementById('cardsGrid').insertAdjacentHTML('beforeend', createCardElement(card));
                });
                
                if (data.meta.current_page < data.meta.last_page) {
                    loadMoreContainer.classList.remove('hidden');
                }
            }
            
            isLoading = false;
        })
        .catch(error => {
            console.error('{{ __('catalog.error_loading_cards') }}', error);
            isLoading = false;
            loadingState.classList.add('hidden');
        });
}

function populateRarityFilter(cards) {
    const rarities = [...new Set(cards.map(c => c.rarity).filter(Boolean))];
    const raritySelect = document.getElementById('rarityFilter');
    rarities.forEach(rarity => {
        const option = document.createElement('option');
        option.value = rarity;
        option.textContent = rarity;
        raritySelect.appendChild(option);
    });
}

function createCardElement(card) {
    const imageUrl = card.image_large_url || card.image_small_url || 'https://via.placeholder.com/245x342/1a1a19/666?text={{ __('catalog.no_image') }}';
    
    let badgeHtml = '';
    if (game === 'lorcana' && card.ink_color) {
        badgeHtml = `<span class="absolute top-2 left-2 px-2 py-1 bg-black/80 text-xs text-white rounded-full">${card.ink_color}</span>`;
    }
    
    // Price display (only for Advanced/Premium users)
    let priceHtml = '';
    if (userCanSeePrices && card.price_eur) {
        let displayPrice = parseFloat(card.price_eur);
        let displayCurrency = 'EUR';
        let needsConversion = preferredCurrency !== 'EUR';
        
        if (needsConversion) {
            displayPrice = convertPrice(displayPrice, 'EUR', preferredCurrency);
            displayCurrency = preferredCurrency;
            const originalPrice = parseFloat(card.price_eur).toFixed(2);
            priceHtml = `<span class="text-xs font-semibold text-green-400 flex-shrink-0">${formatPrice(displayPrice, displayCurrency)} <span class="text-gray-500 font-normal">(€${originalPrice})</span></span>`;
        } else {
            priceHtml = `<span class="text-xs font-semibold text-green-400 flex-shrink-0">${formatPrice(displayPrice, displayCurrency)}</span>`;
        }
    }
    
    return `
        <a href="/${game}/cards/${card.cmapi_id}" class="block group">
            <div class="bg-black/50 border border-white/20 rounded-lg overflow-hidden hover:border-blue-400 transition shadow-lg relative">
                ${badgeHtml}
                <div class="aspect-[245/342]">
                    <img 
                        src="${imageUrl}" 
                        alt="${card.name}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    >
                </div>
                <div class="p-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-sm font-semibold text-white group-hover:text-blue-400 transition truncate flex-1">
                            ${card.name}
                        </div>
                        ${priceHtml}
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>#${card.number}</span>
                        ${card.rarity ? `<span>${card.rarity}</span>` : ''}
                    </div>
                </div>
            </div>
        </a>
    `;
}
</script>
@endsection
