@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('tcgdex.sets.index') }}" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Sets
            </a>
        </div>

        <!-- Set Header -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
            <div class="px-6 py-6 flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-white">{{ $set->getLocalizedName() }}</h1>
                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-300">
                        <span class="px-3 py-1 bg-white/10 border border-white/20 rounded-full font-semibold">{{ $set->tcgdex_id }}</span>
                        @if($set->series)
                            <span class="text-blue-400">{{ $set->series }}</span>
                        @endif
                        @if($set->release_date)
                            <span>Released: {{ $set->release_date->format('F j, Y') }}</span>
                        @endif
                        <span>{{ $set->cards_count }} cards in DB</span>
                        @if($set->card_count_official)
                            <span class="text-gray-500">({{ $set->card_count_official }} official)</span>
                        @endif
                    </div>
                </div>
                @if($set->logo_url)
                    <div class="ml-6 flex-shrink-0">
                        <img 
                            src="{{ $set->logo_url }}" 
                            alt="{{ $set->getLocalizedName() }} logo" 
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
                        placeholder="Search cards by name, number, or illustrator..." 
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                
                <!-- Filters -->
                <div class="flex gap-3">
                    <select id="rarityFilter" class="px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white text-sm">
                        <option value="">All Rarities</option>
                    </select>
                    <select id="typeFilter" class="px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white text-sm">
                        <option value="">All Types</option>
                    </select>
                    <button onclick="clearFilters()" class="px-3 py-2 bg-white/10 hover:bg-white/20 text-gray-300 rounded-lg text-sm transition">
                        Clear Filters
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
            Loading cards...
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
            <p class="text-lg">No cards found</p>
        </div>

        <!-- Load More Button -->
        <div id="loadMoreContainer" class="text-center hidden">
            <button 
                id="loadMoreBtn" 
                class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-lg"
            >
                Load More
            </button>
        </div>
    </div>
</div>

<script>
const SET_ID = '{{ $set->tcgdex_id }}';

let currentPage = 1;
let currentQuery = '';
let currentRarity = '';
let currentType = '';
let lastPage = 1;
let debounceTimer = null;
let isLoading = false;

const searchInput = document.getElementById('searchInput');
const rarityFilter = document.getElementById('rarityFilter');
const typeFilter = document.getElementById('typeFilter');
const loadingState = document.getElementById('loadingState');
const cardsSection = document.getElementById('cardsSection');
const cardsGrid = document.getElementById('cardsGrid');
const noResults = document.getElementById('noResults');
const loadMoreContainer = document.getElementById('loadMoreContainer');
const loadMoreBtn = document.getElementById('loadMoreBtn');

// Debounced search
searchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentQuery = e.target.value;
        currentPage = 1;
        loadCards(true);
    }, 300);
});

// Filter changes
rarityFilter.addEventListener('change', () => {
    currentRarity = rarityFilter.value;
    currentPage = 1;
    loadCards(true);
});

typeFilter.addEventListener('change', () => {
    currentType = typeFilter.value;
    currentPage = 1;
    loadCards(true);
});

// Clear filters
function clearFilters() {
    searchInput.value = '';
    rarityFilter.value = '';
    typeFilter.value = '';
    currentQuery = '';
    currentRarity = '';
    currentType = '';
    currentPage = 1;
    loadCards(true);
}

// Load more
loadMoreBtn.addEventListener('click', () => {
    if (currentPage < lastPage) {
        currentPage++;
        loadCards(false);
    }
});

// Fetch cards
async function loadCards(replace = true) {
    if (isLoading) return;
    isLoading = true;

    loadingState.classList.remove('hidden');
    if (replace) {
        cardsSection.classList.add('hidden');
        noResults.classList.add('hidden');
    }

    try {
        const url = new URL(`/tcgdex/sets/${SET_ID}/cards/search`, window.location.origin);
        url.searchParams.append('page', currentPage);
        if (currentQuery) url.searchParams.append('query', currentQuery);
        if (currentRarity) url.searchParams.append('rarity', currentRarity);
        if (currentType) url.searchParams.append('type', currentType);

        const response = await fetch(url);
        const data = await response.json();

        if (replace) {
            cardsGrid.innerHTML = '';
        }

        if (data.data.length === 0 && currentPage === 1) {
            cardsSection.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            cardsSection.classList.remove('hidden');
            noResults.classList.add('hidden');

            data.data.forEach(card => {
                const cardEl = createCardElement(card);
                cardsGrid.appendChild(cardEl);
            });

            lastPage = data.meta.last_page;
            
            if (currentPage < lastPage) {
                loadMoreContainer.classList.remove('hidden');
            } else {
                loadMoreContainer.classList.add('hidden');
            }

            // Populate filters from cards
            if (currentPage === 1 && replace) {
                populateFilters(data.data);
            }
        }
    } catch (error) {
        console.error('Error loading cards:', error);
        alert('Failed to load cards. Please try again.');
    } finally {
        loadingState.classList.add('hidden');
        isLoading = false;
    }
}

// Create card element
function createCardElement(card) {
    const div = document.createElement('div');
    div.className = 'group cursor-pointer';
    div.onclick = () => {
        window.location.href = `/tcgdex/cards/${card.tcgdex_id}`;
    };

    // Use high quality webp if available
    const imageUrl = card.hd_image_url || (card.image_large_url ? card.image_large_url + '/high.webp' : null) || card.image_small_url;
    const hasHdImage = !!card.hd_image_url || !!card.image_large_url;
    
    div.innerHTML = `
        <div class="bg-black/40 border border-white/10 rounded-xl overflow-hidden hover:border-blue-500/50 hover:shadow-xl hover:scale-105 transition-all">
            ${imageUrl ? `
            <div class="aspect-[5/7] relative">
                <img 
                    src="${imageUrl}" 
                    alt="${escapeHtml(card.name)}" 
                    class="w-full h-full object-contain"
                    loading="lazy"
                />
            </div>
            ` : `
            <div class="aspect-[5/7] bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                <span class="text-gray-500 text-4xl font-bold">#${escapeHtml(card.local_id || '?')}</span>
            </div>
            `}
            <div class="p-2 bg-black/60">
                <div class="text-xs font-mono text-gray-400 mb-1">#${escapeHtml(card.local_id || card.number || '?')}</div>
                <div class="text-sm font-semibold text-white line-clamp-1 group-hover:text-blue-400">${escapeHtml(card.name)}</div>
                ${card.rarity ? `<div class="text-xs text-gray-500 mt-1">${escapeHtml(card.rarity)}</div>` : ''}
            </div>
        </div>
    `;

    return div;
}

// Populate filters dynamically
function populateFilters(cards) {
    const rarities = new Set();
    const types = new Set();

    cards.forEach(card => {
        if (card.rarity) rarities.add(card.rarity);
        if (card.types) card.types.forEach(type => types.add(type));
    });

    // Only populate if filters are empty
    if (rarityFilter.options.length === 1) {
        Array.from(rarities).sort().forEach(rarity => {
            const option = document.createElement('option');
            option.value = rarity;
            option.textContent = rarity;
            rarityFilter.appendChild(option);
        });
    }

    if (typeFilter.options.length === 1) {
        Array.from(types).sort().forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            typeFilter.appendChild(option);
        });
    }
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Load initial cards
loadCards();
</script>
@endsection
