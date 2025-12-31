@extends('layouts.app')

@section('content')
<style>
.tab-button {
    border-color: transparent;
    color: #9ca3af;
}
.tab-button:hover {
    color: white;
}
.tab-button.active {
    border-color: #3b82f6;
    color: #60a5fa;
}
</style>

<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl">
            <!-- Header -->
            <div class="border-b border-white/10 px-6 py-4">
                <h1 class="text-3xl font-bold text-white">TCGdex Sets</h1>
                <p class="mt-1 text-sm text-gray-300">Browse all Pokémon TCG sets from TCGdex</p>
            </div>

            <!-- Tabs -->
            <div class="border-b border-white/10 px-6">
                <div class="flex gap-4">
                    <button 
                        onclick="switchTab('all')" 
                        id="tab-all"
                        class="tab-button px-4 py-3 text-sm font-medium border-b-2 transition-colors active"
                    >
                        All Sets
                    </button>
                    <button 
                        onclick="switchTab('coming-soon')" 
                        id="tab-coming-soon"
                        class="tab-button px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                    >
                        Coming Soon
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="px-6 py-4 border-b border-white/10">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search sets..." 
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
                <div id="setsList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
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
let currentTab = 'all';
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

// Tab switching
function switchTab(tab) {
    currentTab = tab;
    currentPage = 1;
    
    // Update tab UI
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-blue-500', 'text-blue-400');
        btn.classList.add('border-transparent', 'text-gray-400', 'hover:text-white');
    });
    
    const activeTab = document.getElementById(`tab-${tab}`);
    activeTab.classList.add('active', 'border-blue-500', 'text-blue-400');
    activeTab.classList.remove('border-transparent', 'text-gray-400', 'hover:text-white');
    
    loadSets(true);
}

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
        const url = new URL('{{ route('tcgdex.sets.search') }}');
        url.searchParams.append('page', currentPage);
        url.searchParams.append('tab', currentTab);
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
    const card = document.createElement('div');
    card.className = 'bg-black/40 border border-white/10 rounded-xl overflow-hidden hover:border-blue-500/50 hover:shadow-xl transition-all cursor-pointer group';
    card.onclick = () => {
        window.location.href = `/tcgdex/sets/${set.tcgdex_id}`;
    };

    // For logos and symbols, add .webp extension (not /high.webp)
    let logoUrl = null;
    if (set.logo_url) {
        logoUrl = set.logo_url + '.webp';
    } else if (set.symbol_url) {
        logoUrl = set.symbol_url + '.webp';
    }
    
    const badgeId = `badge-${set.tcgdex_id}`;
    
    card.innerHTML = `
        <div class="aspect-video bg-gradient-to-br from-gray-900 to-black relative flex items-center justify-center p-4">
            ${logoUrl ? `
            <img 
                src="${logoUrl}" 
                alt="${escapeHtml(set.name)}" 
                class="max-w-full max-h-full object-contain transition-transform group-hover:scale-105"
                onerror="this.style.display='none'; document.getElementById('${badgeId}').style.display='flex';"
            />
            ` : ''}
            <div id="${badgeId}" class="w-24 h-24 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg" style="display:${logoUrl ? 'none' : 'flex'};">
                ${escapeHtml(set.tcgdex_id).substring(0, 3).toUpperCase()}
            </div>
        </div>
        <div class="p-4">
            <h3 class="text-white font-semibold text-base mb-2 line-clamp-2 group-hover:text-blue-400 transition-colors">
                ${escapeHtml(set.name)}
            </h3>
            ${set.series ? `<div class="text-xs text-gray-500 mb-2">${escapeHtml(set.series)}</div>` : ''}
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span class="px-2 py-1 bg-white/5 rounded-md font-mono">${escapeHtml(set.tcgdex_id)}</span>
                <span>${set.cards_count || set.card_count_official || 0} cards</span>
            </div>
            ${set.release_date ? `<div class="mt-2 text-xs text-gray-500">${set.release_date}</div>` : ''}
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
