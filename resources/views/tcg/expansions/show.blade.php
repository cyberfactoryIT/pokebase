@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('tcg.expansions.index') }}" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('catalogue.back_to_expansions') }}
            </a>
        </div>

        <!-- Expansion Header -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
            <div class="px-6 py-6">
                <h1 class="text-4xl font-bold text-white">{{ $expansion->name }}</h1>
                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-300">
                    @if($expansion->abbreviation)
                        <span class="px-3 py-1 bg-white/10 border border-white/20 rounded-full font-semibold">{{ $expansion->abbreviation }}</span>
                    @endif
                    @if($expansion->published_on)
                        <span>{{ __('catalogue.released') }}: {{ $expansion->published_on->format('F j, Y') }}</span>
                    @endif
                    <span>{{ __('catalogue.cards_count', ['count' => $expansion->products_count]) }}</span>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
            <div class="px-6 py-4">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="{{ __('catalogue.search_cards') }}" 
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
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
            <h2 class="text-2xl font-bold text-white mb-4 px-2">{{ __('catalogue.section_cards') }}  

            </h2>
            <div id="cardsGrid" class="grid gap-3 mb-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Others Grid -->
        <div id="othersSection" class="mb-8 hidden">
            <h2 class="text-2xl font-bold text-white mb-4 px-2">{{ __('catalogue.section_others') }}</h2>
            <div id="othersGrid" class="grid gap-3 mb-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
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
                Load More Cards
            </button>
        </div>
    </div>
</div>

<script>
const groupId = {{ $expansion->group_id }};
let currentPage = 1;
let currentQuery = '';
let lastPage = 1;
let debounceTimer = null;
let isLoading = false;

const searchInput = document.getElementById('searchInput');
const loadingState = document.getElementById('loadingState');
const cardsSection = document.getElementById('cardsSection');
const cardsGrid = document.getElementById('cardsGrid');
const othersSection = document.getElementById('othersSection');
const othersGrid = document.getElementById('othersGrid');
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
        othersSection.classList.add('hidden');
        noResults.classList.add('hidden');
    }

    try {
        const url = new URL(`/tcg/expansions/${groupId}/cards/search`, window.location.origin);
        url.searchParams.append('page', currentPage);
        if (currentQuery) {
            url.searchParams.append('query', currentQuery);
        }

        const response = await fetch(url);
        const data = await response.json();

        if (replace) {
            cardsGrid.innerHTML = '';
            othersGrid.innerHTML = '';
        }

        if (data.data.length === 0 && currentPage === 1) {
            cardsSection.classList.add('hidden');
            othersSection.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');

            // Separate cards from other products
            const cards = [];
            const others = [];
            
            data.data.forEach(card => {
                if (card.card_number && card.card_number.trim() !== '') {
                    cards.push(card);
                } else {
                    others.push(card);
                }
            });

            // Show cards section if there are cards
            if (cards.length > 0) {
                cardsSection.classList.remove('hidden');
                cards.forEach(card => {
                    const cardElement = createCardTile(card);
                    cardsGrid.appendChild(cardElement);
                });
            } else if (replace) {
                cardsSection.classList.add('hidden');
            }

            // Show others section if there are other products
            if (others.length > 0) {
                othersSection.classList.remove('hidden');
                others.forEach(card => {
                    const cardElement = createCardTile(card);
                    othersGrid.appendChild(cardElement);
                });
            } else if (replace) {
                othersSection.classList.add('hidden');
            }

            lastPage = data.meta.last_page;
            
            // Show/hide load more button
            if (currentPage < lastPage) {
                loadMoreContainer.classList.remove('hidden');
            } else {
                loadMoreContainer.classList.add('hidden');
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

// Create card tile
function createCardTile(card) {
    const div = document.createElement('div');
    div.className = 'bg-[#1a1a19] border border-white/10 rounded-lg hover:border-white/30 hover:shadow-xl transition cursor-pointer overflow-hidden group';
    div.onclick = () => {
        window.location.href = `/tcg/cards/${card.product_id}`;
    };

    const imageUrl = card.image_url || 'https://via.placeholder.com/245x342/1a1a19/666?text=No+Image';

    div.innerHTML = `
        <div class="aspect-[245/342] bg-black/50 overflow-hidden">
            <img 
                src="${escapeHtml(imageUrl)}" 
                alt="${escapeHtml(card.name)}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                loading="lazy"
                onerror="this.src='https://via.placeholder.com/245x342/1a1a19/666?text=No+Image'"
            >
        </div>
        <div class="p-2">
            <h3 class="text-xs font-semibold text-white truncate group-hover:text-blue-400 transition">
                ${escapeHtml(card.name)}
            </h3>
            ${card.card_number ? `<p class="text-xs text-gray-400 mt-0.5">#${escapeHtml(card.card_number)}</p>` : ''}
        </div>
    `;

    return div;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load initial results
loadCards();
</script>
@endsection
