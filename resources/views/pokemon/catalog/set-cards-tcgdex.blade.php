@extends('layouts.app')

@section('content')
<div class="py-12 bg-black min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Back Link -->
        <div class="mb-4">
            <a href="{{ route('pokemon.sets') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← {{ __('catalog.back_to_sets') }}
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
                        <p class="text-gray-400">{{ __('catalog.series') }}: {{ $set->series_name['en'] ?? 'N/A' }}</p>
                        <p class="text-gray-400">{{ __('catalog.total_cards') }}: {{ $set->card_count_total ?? 0 }}</p>
                        @if($set->released_at)
                            <p class="text-gray-400">{{ __('catalog.released') }}: {{ $set->released_at->format('F j, Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Section -->
        <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden">
            <!-- Search Bar -->
            <div class="px-6 py-4 border-b border-white/10">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search cards by name or number..." 
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="px-6 py-8 text-center text-gray-400 hidden">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading cards...
            </div>

            <!-- Cards Grid -->
            <div id="resultsContainer" class="p-6">
                <div id="cardsList" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- No Results -->
            <div id="noResults" class="px-6 py-12 text-center text-gray-400 hidden">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2">No cards found</p>
            </div>

            <!-- Load More Button -->
            <div id="loadMoreContainer" class="px-6 py-4 border-t border-white/10 text-center hidden">
                <button 
                    id="loadMoreBtn" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-lg"
                >
                    Load More
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const setId = '{{ $set->tcgdex_id }}';
const isAuth = {{ auth()->check() ? 'true' : 'false' }};
const userCurrency = '{{ auth()->check() ? (auth()->user()->preferred_currency ?? 'EUR') : 'EUR' }}';
const isAdvanced = {{ auth()->check() && (auth()->user()->isAdvanced() || auth()->user()->isPremium()) ? 'true' : 'false' }};

let currentPage = 1;
let currentQuery = '';
let lastPage = 1;
let debounceTimer = null;
let isLoading = false;

const searchInput = document.getElementById('searchInput');
const loadingState = document.getElementById('loadingState');
const resultsContainer = document.getElementById('resultsContainer');
const cardsList = document.getElementById('cardsList');
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
        resultsContainer.classList.add('hidden');
        noResults.classList.add('hidden');
    }

    try {
        const url = new URL(`/pokemon/sets/${setId}/cards/search`, window.location.origin);
        url.searchParams.append('page', currentPage);
        if (currentQuery) {
            url.searchParams.append('query', currentQuery);
        }

        const response = await fetch(url);
        const data = await response.json();

        if (replace) {
            cardsList.innerHTML = '';
        }

        if (data.data.length === 0 && currentPage === 1) {
            resultsContainer.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            resultsContainer.classList.remove('hidden');
            noResults.classList.add('hidden');

            data.data.forEach(card => {
                const cardElement = createCardElement(card);
                cardsList.appendChild(cardElement);
            });

            lastPage = data.meta.last_page;
            
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

// Create card element
function createCardElement(card) {
    const div = document.createElement('div');
    div.className = 'relative bg-[#1a1a19] border border-white/20 rounded-lg p-3 hover:shadow-xl hover:border-white/40 transition-all group';
    
    let interactionButtons = '';
    if (isAuth) {
        interactionButtons = `
            <div class="flex gap-1 ml-2">
                <button onclick="event.preventDefault(); event.stopPropagation(); toggleLike('${card.tcgdex_id}', this)" 
                    class="p-1 ${card.is_liked ? 'bg-red-600' : 'bg-gray-700/90'} hover:bg-red-500 rounded text-white transition" 
                    data-card-id="${card.tcgdex_id}">
                    <svg class="w-3 h-3" fill="${card.is_liked ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
                <button onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist('${card.tcgdex_id}', this)" 
                    class="p-1 ${card.is_wishlisted ? 'bg-purple-600' : 'bg-gray-700/90'} hover:bg-purple-500 rounded text-white transition" 
                    data-card-id="${card.tcgdex_id}">
                    <svg class="w-3 h-3" fill="${card.is_wishlisted ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                </button>
                <button onclick="event.preventDefault(); event.stopPropagation(); toggleWatch('${card.tcgdex_id}', this)" 
                    class="p-1 ${card.is_watched ? 'bg-yellow-600' : 'bg-gray-700/90'} hover:bg-yellow-500 rounded text-white transition" 
                    data-card-id="${card.tcgdex_id}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
        `;
    }

    let priceDisplay = '';
    if (card.price_eur && isAuth && isAdvanced) {
        const price = parseFloat(card.price_eur);
        priceDisplay = `<span class="text-sm font-semibold text-green-400 mt-2">€${price.toFixed(2)}</span>`;
    }
    
    div.innerHTML = `
        <a href="/pokemon/cards/${card.tcgdex_id}" class="block">
            ${card.image_small_url ? `
                <img src="${card.image_small_url}/high.webp" 
                     alt="${escapeHtml(card.name)}"
                     class="w-full rounded mb-2"
                     loading="lazy">
            ` : ''}
            
            <div class="text-sm">
                <div class="flex items-center justify-between mb-1">
                    <p class="font-semibold truncate text-white flex-1">${escapeHtml(card.name)}</p>
                    ${interactionButtons}
                </div>
                <p class="text-gray-400 text-xs">
                    ${card.number || card.local_id}
                    ${card.rarity ? ' ' + escapeHtml(card.rarity) : ''}
                    ${priceDisplay}
                </p>
            </div>
        </a>
    `;

    return div;
}

// Interaction functions
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
            if (data.status === 'added') {
                button.classList.remove('bg-gray-700/90');
                button.classList.add('bg-red-600');
                svg.setAttribute('fill', 'currentColor');
            } else {
                button.classList.remove('bg-red-600');
                button.classList.add('bg-gray-700/90');
                svg.setAttribute('fill', 'none');
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
            if (data.status === 'added') {
                button.classList.remove('bg-gray-700/90');
                button.classList.add('bg-purple-600');
                svg.setAttribute('fill', 'currentColor');
            } else {
                button.classList.remove('bg-purple-600');
                button.classList.add('bg-gray-700/90');
                svg.setAttribute('fill', 'none');
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
            if (data.status === 'watched') {
                button.classList.remove('bg-gray-700/90');
                button.classList.add('bg-yellow-600');
            } else {
                button.classList.remove('bg-yellow-600');
                button.classList.add('bg-gray-700/90');
            }
        }
    } catch (error) {
        console.error('Error toggling watch:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Load initial results
loadCards();
</script>
@endsection
