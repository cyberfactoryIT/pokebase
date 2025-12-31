@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl">
            <!-- Header -->
            <div class="border-b border-white/10 px-6 py-4">
                <h1 class="text-3xl font-bold text-white">{{ __('catalogue.expansions_title') }}</h1>
                <p class="mt-1 text-sm text-gray-300">{{ __('catalogue.expansions_subtitle') }}</p>
            </div>

            <!-- Search Bar -->
            <div class="px-6 py-4 border-b border-white/10">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="{{ __('catalogue.search_expansions') }}" 
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
                {{ __('catalogue.loading_expansions') }}
            </div>

            <!-- Coming Soon Section -->
            <div id="upcomingSection" class="px-6 py-4 border-b border-white/10 hidden">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">🚀 Coming Soon</h3>
                <div id="upcomingList" class="flex flex-wrap gap-2">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Results Grid -->
            <div id="resultsContainer" class="p-6">
                <div id="expansionsList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- No Results -->
            <div id="noResults" class="px-6 py-12 text-center text-gray-400 hidden">
                <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2">{{ __('catalogue.no_expansions_found') }}</p>
            </div>

            <!-- Load More Button -->
            <div id="loadMoreContainer" class="px-6 py-4 border-t border-white/10 text-center hidden">
                <button 
                    id="loadMoreBtn" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-lg"
                >
                    {{ __('catalogue.load_more') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const i18n = {
    cardsCount: '{{ __('catalogue.cards_count', ['count' => ':count']) }}'.replace(':count', '{count}')
};

let currentPage = 1;
let currentQuery = '';
let lastPage = 1;
let debounceTimer = null;
let isLoading = false;

const searchInput = document.getElementById('searchInput');
const loadingState = document.getElementById('loadingState');
const resultsContainer = document.getElementById('resultsContainer');
const expansionsList = document.getElementById('expansionsList');
const noResults = document.getElementById('noResults');
const loadMoreContainer = document.getElementById('loadMoreContainer');
const loadMoreBtn = document.getElementById('loadMoreBtn');
const upcomingSection = document.getElementById('upcomingSection');
const upcomingList = document.getElementById('upcomingList');

// Debounced search
searchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentQuery = e.target.value;
        currentPage = 1;
        loadExpansions(true);
    }, 300);
});

// Load more
loadMoreBtn.addEventListener('click', () => {
    if (currentPage < lastPage) {
        currentPage++;
        loadExpansions(false);
    }
});

// Fetch expansions
async function loadExpansions(replace = true) {
    if (isLoading) return;
    isLoading = true;

    loadingState.classList.remove('hidden');
    if (replace) {
        resultsContainer.classList.add('hidden');
        noResults.classList.add('hidden');
    }

    try {
        const url = new URL('{{ route('tcg.expansions.search') }}');
        url.searchParams.append('page', currentPage);
        if (currentQuery) {
            url.searchParams.append('query', currentQuery);
        }

        const response = await fetch(url);
        const data = await response.json();

        if (replace) {
            expansionsList.innerHTML = '';
        }

        if (data.data.length === 0 && currentPage === 1) {
            resultsContainer.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            resultsContainer.classList.remove('hidden');
            noResults.classList.add('hidden');

            // Show upcoming releases on first page only
            if (currentPage === 1 && data.upcoming && data.upcoming.length > 0) {
                upcomingList.innerHTML = '';
                data.upcoming.forEach(item => {
                    const link = document.createElement('a');
                    link.href = `/tcg/expansions/${item.group_id}`;
                    link.className = 'inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-lg hover:bg-blue-500/20 transition text-xs font-medium';
                    link.innerHTML = `
                        <span>${escapeHtml(item.name)}</span>
                        <span class="text-blue-300/70">(${item.published_on})</span>
                    `;
                    upcomingList.appendChild(link);
                });
                upcomingSection.classList.remove('hidden');
            } else if (currentPage === 1) {
                upcomingSection.classList.add('hidden');
            }

            data.data.forEach(expansion => {
                const row = createExpansionRow(expansion);
                expansionsList.appendChild(row);
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
        console.error('Error loading expansions:', error);
        alert('Failed to load expansions. Please try again.');
    } finally {
        loadingState.classList.add('hidden');
        isLoading = false;
    }
}

// Create expansion card
function createExpansionRow(expansion) {
    const card = document.createElement('div');
    card.className = 'bg-black/40 border border-white/10 rounded-xl overflow-hidden hover:border-blue-500/50 hover:shadow-xl transition-all cursor-pointer group';
    card.onclick = () => {
        window.location.href = `/tcg/expansions/${expansion.group_id}`;
    };

    const localLogoPath = `/images/logos/${expansion.abbreviation || 'unknown'}-logo.png`;
    const logoUrl = expansion.logo_url || localLogoPath;
    const badgeId = `badge-${expansion.group_id}`;
    
    // Format Cardmarket value
    const valueDisplay = expansion.cardmarket_value > 0 
        ? `<div class="mt-2 flex items-center justify-between text-xs">
            <span class="text-gray-500">Est. Value:</span>
            <span class="text-green-400 font-semibold">€${expansion.cardmarket_value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
           </div>
           <div class="mt-1 text-xs text-gray-600">
            ${expansion.cards_printed} printed cards
           </div>`
        : '';
    
    card.innerHTML = `
        <div class="aspect-video bg-gradient-to-br from-gray-900 to-black relative flex items-center justify-center p-4">
            <img 
                src="${logoUrl}" 
                alt="${escapeHtml(expansion.name)}" 
                class="max-w-full max-h-full object-contain transition-transform group-hover:scale-105"
                onerror="this.onerror=null; this.src='${localLogoPath}'; this.onerror=function() { this.style.display='none'; document.getElementById('${badgeId}').style.display='flex'; };"
            />
            <div id="${badgeId}" class="w-24 h-24 ${expansion.color} rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg" style="display:none;">
                ${escapeHtml(expansion.abbreviation || '?').substring(0, 3)}
            </div>
        </div>
        <div class="p-4">
            <h3 class="text-white font-semibold text-base mb-2 line-clamp-2 group-hover:text-blue-400 transition-colors">
                ${escapeHtml(expansion.name)}
            </h3>
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span class="px-2 py-1 bg-white/5 rounded-md font-mono">${escapeHtml(expansion.abbreviation || 'N/A')}</span>
                <span>${i18n.cardsCount.replace('{count}', expansion.products_count.toLocaleString())}</span>
            </div>
            ${expansion.published_on ? `<div class="mt-2 text-xs text-gray-500">${expansion.published_on}</div>` : ''}
            ${valueDisplay}
        </div>
    `;

    return card;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load initial results
loadExpansions();
</script>
@endsection
