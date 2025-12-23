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

            <!-- Results Table -->
            <div id="resultsContainer" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10">
                    <thead class="bg-black/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider w-20">{{ __('catalogue.table_icon') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">{{ __('catalogue.table_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">{{ __('catalogue.table_code') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">{{ __('catalogue.table_published') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">{{ __('catalogue.table_cards') }}</th>
                        </tr>
                    </thead>
                    <tbody id="expansionsList" class="bg-black/20 divide-y divide-white/10">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
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

// Create expansion row
function createExpansionRow(expansion) {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-white/5 cursor-pointer transition-colors';
    tr.onclick = () => {
        window.location.href = `/tcg/expansions/${expansion.group_id}`;
    };

    const logoPath = `/images/logos/${expansion.abbreviation || 'unknown'}-logo.png`;
    const badgeId = `badge-${expansion.group_id}`;
    
    tr.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <img 
                src="${logoPath}" 
                alt="${escapeHtml(expansion.name)}" 
                class="w-12 h-12 object-contain"
                onerror="this.style.display='none'; document.getElementById('${badgeId}').style.display='flex';"
            />
            <div id="${badgeId}" class="w-12 h-12 ${expansion.color} rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-md" style="display:none;">
                ${escapeHtml(expansion.abbreviation || '?').substring(0, 3)}
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-white">${escapeHtml(expansion.name)}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-white/10 text-gray-300">
                ${escapeHtml(expansion.abbreviation || 'N/A')}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
            ${expansion.published_on || 'N/A'}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
            ${i18n.cardsCount.replace('{count}', expansion.products_count.toLocaleString())}
        </td>
    `;

    return tr;
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
