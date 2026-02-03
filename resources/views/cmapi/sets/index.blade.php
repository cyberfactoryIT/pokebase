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
                <h1 class="text-3xl font-bold text-white">
                    @if($game === 'lorcana')
                        {{ __('catalog.disney_lorcana_sets') }}
                    @else
                        {{ __('catalog.onepiece_sets') }}
                    @endif
                </h1>
                <p class="mt-1 text-sm text-gray-300">
                    {{ __('catalog.browse_all_sets') }}{{ $game === 'lorcana' ? 'Lorcana' : 'One Piece' }}{{ __('catalog.sets_from_api') }}
                </p>
            </div>

            <!-- Search Bar -->
            <div class="px-6 py-4 border-b border-white/10">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="{{ __('catalog.search_sets_placeholder') }}" 
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
                {{ __('catalog.loading_sets') }}
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
                <p class="mt-2">{{ __('catalog.no_sets_found') }}</p>
            </div>

            <!-- Load More Button -->
            <div id="loadMoreContainer" class="px-6 py-4 border-t border-white/10 text-center hidden">
                <button 
                    id="loadMoreBtn" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-lg"
                >
                    {{ __('catalog.load_more') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentTab = 'all';
let searchQuery = '';
let isLoading = false;
const game = '{{ $game }}';

document.addEventListener('DOMContentLoaded', function() {
    loadSets(1);
    
    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = e.target.value;
            loadSets(1);
        }, 300);
    });
    
    // Load More
    document.getElementById('loadMoreBtn').addEventListener('click', function() {
        loadSets(currentPage + 1, true);
    });
});

function loadSets(page = 1, append = false) {
    if (isLoading) return;
    isLoading = true;
    
    const loadingState = document.getElementById('loadingState');
    const resultsContainer = document.getElementById('resultsContainer');
    const noResults = document.getElementById('noResults');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    
    if (!append) {
        loadingState.classList.remove('hidden');
        resultsContainer.classList.add('hidden');
        noResults.classList.add('hidden');
    }
    loadMoreContainer.classList.add('hidden');
    
    const params = new URLSearchParams({
        page: page,
        game: game,
        query: searchQuery
    });
    
    fetch(`/{{ $game }}/sets/search?${params}`)
        .then(response => response.json())
        .then(data => {
            currentPage = data.meta.current_page;
            
            if (!append) {
                document.getElementById('setsList').innerHTML = '';
            }
            
            if (data.data.length === 0 && !append) {
                loadingState.classList.add('hidden');
                noResults.classList.remove('hidden');
            } else {
                loadingState.classList.add('hidden');
                resultsContainer.classList.remove('hidden');
                
                data.data.forEach(set => {
                    document.getElementById('setsList').insertAdjacentHTML('beforeend', createSetCard(set));
                });
                
                if (data.meta.current_page < data.meta.last_page) {
                    loadMoreContainer.classList.remove('hidden');
                }
            }
            
            isLoading = false;
        })
        .catch(error => {
            console.error('{{ __('catalog.error_loading_sets') }}', error);
            isLoading = false;
            loadingState.classList.add('hidden');
        });
}

function createSetCard(set) {
    const releaseDate = set.release_date ? new Date(set.release_date).toLocaleDateString() : '{{ __('catalog.tba') }}';
    
    return `
        <a href="/${game}/sets/${set.cmapi_episode}" class="block group">
            <div class="bg-black/50 border border-white/20 rounded-xl overflow-hidden hover:border-blue-400 transition shadow-lg h-full flex flex-col">
                ${set.logo_url ? `
                <div class="aspect-video bg-gradient-to-br from-gray-900 to-black p-4 flex items-center justify-center">
                    <img 
                        src="${set.logo_url}" 
                        alt="${set.name}"
                        class="max-w-full max-h-full object-contain"
                        onerror="this.parentElement.innerHTML='<div class=\\'text-gray-600 text-sm\\'>{{ __('catalog.no_logo') }}</div>'"
                    >
                </div>
                ` : ''}
                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold text-white group-hover:text-blue-400 transition mb-2 line-clamp-2">
                        ${set.name}
                    </h3>
                    <div class="mt-auto space-y-1 text-xs text-gray-400">
                        <div class="flex justify-between">
                            <span>{{ __('catalog.episode_prefix') }}${set.cmapi_episode}</span>
                            <span>${set.cards_count}{{ __('catalog.cards_suffix') }}</span>
                        </div>
                        <div class="text-gray-500">${releaseDate}</div>
                    </div>
                </div>
            </div>
        </a>
    `;
}
</script>
@endsection
