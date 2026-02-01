@extends('layouts.app')

@section('content')
<div class="py-12 bg-black min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="border-b border-white/10 px-6 py-4">
                <h1 class="text-3xl font-bold text-white">Pokémon TCG Sets</h1>
                <p class="mt-1 text-sm text-gray-300">Browse all Pokémon TCG sets</p>
            </div>

            <!-- Search Bar -->
            <div class="px-6 py-4 border-b border-white/10">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search sets by name or code..." 
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
                Loading sets...
            </div>

            <!-- Results Grid -->
            <div id="resultsContainer" class="p-6">
                <div id="setsList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- No Results -->
            <div id="noResults" class="px-6 py-12 text-center text-gray-400 hidden">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2">No sets found</p>
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
let currentPage = 1;
let currentQuery = '';
let lastPage = 1;
let debounceTimer = null;
let isLoading = false;

const searchInput = document.getElementById('searchInput');
const loadingState = document.getElementById('loadingState');
const resultsContainer = document.getElementById('resultsContainer');
const setsList = document.getElementById('setsList');
const noResults = document.getElementById('noResults');
const loadMoreContainer = document.getElementById('loadMoreContainer');
const loadMoreBtn = document.getElementById('loadMoreBtn');

// Debounced search
searchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentQuery = e.target.value;
        currentPage = 1;
        loadSets(true);
    }, 300);
});

// Load more
loadMoreBtn.addEventListener('click', () => {
    if (currentPage < lastPage) {
        currentPage++;
        loadSets(false);
    }
});

// Fetch sets
async function loadSets(replace = true) {
    if (isLoading) return;
    isLoading = true;

    loadingState.classList.remove('hidden');
    if (replace) {
        resultsContainer.classList.add('hidden');
        noResults.classList.add('hidden');
    }

    try {
        const url = new URL('{{ route('pokemon.sets.search') }}', window.location.origin);
        url.searchParams.append('page', currentPage);
        if (currentQuery) {
            url.searchParams.append('query', currentQuery);
        }

        const response = await fetch(url);
        const data = await response.json();

        if (replace) {
            setsList.innerHTML = '';
        }

        if (data.data.length === 0 && currentPage === 1) {
            resultsContainer.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            resultsContainer.classList.remove('hidden');
            noResults.classList.add('hidden');

            data.data.forEach(set => {
                const card = createSetCard(set);
                setsList.appendChild(card);
            });

            lastPage = data.meta.last_page;
            
            // Show/hide load more button
            if (currentPage < lastPage) {
                loadMoreContainer.classList.remove('hidden');
            } else {
                loadMoreContainer.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error loading sets:', error);
        alert('Failed to load sets. Please try again.');
    } finally {
        loadingState.classList.add('hidden');
        isLoading = false;
    }
}

// Create set card
function createSetCard(set) {
    const card = document.createElement('a');
    card.href = `/pokemon/sets/${set.tcgdex_id}`;
    card.className = 'block bg-[#1a1a19] border border-white/20 rounded-lg p-4 hover:shadow-xl hover:border-white/40 transition-all';
    
    let logoUrl = null;
    if (set.logo_url) {
        logoUrl = set.logo_url + '.webp';
    }
    
    const badgeId = `badge-${set.tcgdex_id}`;
    
    card.innerHTML = `
        <div class="w-full h-32 flex items-center justify-center mb-4 relative">
            ${logoUrl ? `
            <img 
                src="${logoUrl}" 
                alt="${escapeHtml(set.name)}" 
                class="max-w-full max-h-full object-contain"
                onerror="this.style.display='none'; this.src='${set.logo_url}.webp';"
            />
            ` : `
            <div class="w-24 h-24 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                ${escapeHtml(set.tcgdex_id).substring(0, 3).toUpperCase()}
            </div>
            `}
        </div>
        <h3 class="font-semibold text-lg mb-2 text-white">
            ${escapeHtml(set.name)}
        </h3>
        <div class="text-sm text-gray-400">
            ${set.series ? `<p class="mb-1">${escapeHtml(set.series)}</p>` : ''}
            <p>Cards: ${set.card_count_total || 0}</p>
            ${set.release_date ? `<p class="text-xs mt-1">${set.release_date}</p>` : ''}
        </div>
    `;

    return card;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Load initial results
loadSets();
</script>
@endsection
