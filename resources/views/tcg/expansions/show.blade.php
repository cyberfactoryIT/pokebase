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
                {{ __('tcg/expansions/show.back_to_expansions') }}
            </a>
        </div>

        <!-- Expansion Header -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
            <div class="px-6 py-6 flex items-start justify-between">
                <div class="flex-1">
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
                @if($expansion->abbreviation)
                    <div class="ml-6 flex-shrink-0">
                        <img 
                            src="/images/logos/{{ $expansion->abbreviation }}-logo.png" 
                            alt="{{ $expansion->name }} logo" 
                            class="h-16 w-auto object-contain"
                            onerror="this.style.display='none'"
                        >
                    </div>
                @endif
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
            <div class="px-6 py-4">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="{{ __('tcg/expansions/show.search_cards') }}" 
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" class="bg-blue-600 border border-blue-500 rounded-xl shadow-xl mb-6 p-4 hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-white font-semibold"><span id="selectedCount">0</span> {{ __('tcg/expansions/show.cards_selected') }}</span>
                    <button onclick="clearSelection()" class="text-blue-100 hover:text-white text-sm underline">{{ __('tcg/expansions/show.clear') }}</button>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="openBulkCollectionModal()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('tcg/expansions/show.add_to_collection') }}
                    </button>
                    <button onclick="openBulkDeckModal()" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        {{ __('tcg/expansions/show.add_to_deck') }}
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
            {{ __('tcg/expansions/show.loading_cards') }}
        </div>

        <!-- Cards Grid -->
        <div id="cardsSection" class="mb-8 hidden">
            <h2 class="text-2xl font-bold text-white mb-4 px-2">{{ __('tcg/expansions/show.section_cards') }}</h2>
            <div id="cardsGrid" class="grid gap-3 mb-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Others Grid -->
        <div id="othersSection" class="mb-8 hidden">
            <h2 class="text-2xl font-bold text-white mb-4 px-2">{{ __('tcg/expansions/show.section_others') }}</h2>
            <div id="othersGrid" class="grid gap-3 mb-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- No Results -->
        <div id="noResults" class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-12 text-center text-gray-400 hidden">
            <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg">{{ __('tcg/expansions/show.no_results') }}</p>
        </div>

        <!-- Load More Button -->
        <div id="loadMoreContainer" class="text-center hidden">
            <button 
                id="loadMoreBtn" 
                class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-lg"
            >
                {{ __('tcg/expansions/show.load_more') }}
            </button>
        </div>
    </div>
</div>

<!-- Collection Modal -->
<div id="collectionModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/75 transition-opacity" onclick="closeCollectionModal()"></div>
        <div class="relative bg-[#161615] border border-white/15 rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white">{{ __('tcg/expansions/show.modal_collection_title') }}</h3>
                <button onclick="closeCollectionModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="collectionForm" onsubmit="return submitBulkCollection(event)">
                @csrf
                <input type="hidden" name="product_ids" id="collectionProductIds">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('tcg/expansions/show.quantity_per_card') }}</label>
                        <input type="number" name="quantity" value="1" min="1" max="99" class="w-full px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('tcg/expansions/show.condition') }}</label>
                        <select name="condition" class="w-full px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white">
                            <option value="">{{ __('tcg/expansions/show.condition_standard') }}</option>
                            <option value="mint">{{ __('tcg/expansions/show.condition_mint') }}</option>
                            <option value="near_mint">{{ __('tcg/expansions/show.condition_near_mint') }}</option>
                            <option value="excellent">{{ __('tcg/expansions/show.condition_excellent') }}</option>
                            <option value="good">{{ __('tcg/expansions/show.condition_good') }}</option>
                            <option value="light_played">{{ __('tcg/expansions/show.condition_light_played') }}</option>
                            <option value="played">{{ __('tcg/expansions/show.condition_played') }}</option>
                            <option value="poor">{{ __('tcg/expansions/show.condition_poor') }}</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_foil" value="1" id="isFoil" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded">
                        <label for="isFoil" class="ml-2 text-sm text-gray-300">{{ __('tcg/expansions/show.foil_holo') }}</label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('tcg/expansions/show.notes_optional') }}</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeCollectionModal()" class="flex-1 px-4 py-2 bg-white/10 hover:bg-white/20 text-gray-300 rounded-lg transition">
                        {{ __('tcg/expansions/show.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        {{ __('tcg/expansions/show.add_to_collection') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deck Modal -->
<div id="deckModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/75 transition-opacity" onclick="closeDeckModal()"></div>
        <div class="relative bg-[#161615] border border-white/15 rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-white">{{ __('tcg/expansions/show.modal_deck_title') }}</h3>
                <button onclick="closeDeckModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            @php
                $userDecks = Auth::user()->decks ?? collect();
            @endphp
            
            @if($userDecks->isEmpty())
                <p class="text-gray-400 mb-4">{{ __('tcg/expansions/show.no_decks_yet') }}</p>
                <a href="{{ route('decks.create') }}" class="block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-center">
                    {{ __('tcg/expansions/show.create_first_deck') }}
                </a>
            @else
                <div class="space-y-2 mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('tcg/expansions/show.quantity_per_card') }}</label>
                    <input type="number" id="deckQuantity" value="1" min="1" max="4" class="w-full px-3 py-2 bg-black/50 border border-white/20 rounded-lg text-white mb-4">
                </div>
                
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($userDecks as $deck)
                        <button type="button" onclick="addToDeck({{ $deck->id }})" class="w-full text-left px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg transition group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-white group-hover:text-blue-400">{{ $deck->name }}</div>
                                    @if($deck->format)
                                        <div class="text-sm text-gray-400">{{ ucfirst($deck->format) }}</div>
                                    @endif
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
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
let selectedCards = new Set();

const searchInput = document.getElementById('searchInput');
const loadingState = document.getElementById('loadingState');
const cardsSection = document.getElementById('cardsSection');
const cardsGrid = document.getElementById('cardsGrid');
const othersSection = document.getElementById('othersSection');
const othersGrid = document.getElementById('othersGrid');
const noResults = document.getElementById('noResults');
const loadMoreBtn = document.getElementById('loadMoreBtn');
const loadMoreContainer = document.getElementById('loadMoreContainer');
const bulkActionsBar = document.getElementById('bulkActionsBar');
const selectedCountSpan = document.getElementById('selectedCount');

// Selection management
function toggleCardSelection(productId, checkbox) {
    if (checkbox.checked) {
        selectedCards.add(productId);
    } else {
        selectedCards.delete(productId);
    }
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    if (selectedCards.size > 0) {
        bulkActionsBar.classList.remove('hidden');
        selectedCountSpan.textContent = selectedCards.size;
    } else {
        bulkActionsBar.classList.add('hidden');
    }
}

function clearSelection() {
    selectedCards.clear();
    document.querySelectorAll('.card-checkbox').forEach(cb => cb.checked = false);
    updateBulkActionsBar();
}

// Modal management
function openBulkCollectionModal() {
    document.getElementById('collectionProductIds').value = Array.from(selectedCards).join(',');
    document.getElementById('collectionModal').classList.remove('hidden');
}

function closeCollectionModal() {
    document.getElementById('collectionModal').classList.add('hidden');
}

function openBulkDeckModal() {
    document.getElementById('deckModal').classList.remove('hidden');
}

function closeDeckModal() {
    document.getElementById('deckModal').classList.add('hidden');
}

// Submit bulk collection
async function submitBulkCollection(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const productIds = document.getElementById('collectionProductIds').value.split(',');
        let successCount = 0;
        
        for (const productId of productIds) {
            const data = new FormData();
            data.append('_token', formData.get('_token'));
            data.append('product_id', productId);
            data.append('quantity', formData.get('quantity'));
            if (formData.get('condition')) data.append('condition', formData.get('condition'));
            if (formData.get('is_foil')) data.append('is_foil', '1');
            if (formData.get('notes')) data.append('notes', formData.get('notes'));
            
            const response = await fetch('{{ route("collection.add") }}', {
                method: 'POST',
                body: data
            });
            
            if (response.ok) successCount++;
        }
        
        closeCollectionModal();
        clearSelection();
        alert(`${successCount} card(s) added to collection!`);
    } catch (error) {
        console.error('Error adding to collection:', error);
        alert('Failed to add cards. Please try again.');
    }
}

// Add to deck
async function addToDeck(deckId) {
    const quantity = document.getElementById('deckQuantity').value;
    const productIds = Array.from(selectedCards);
    
    try {
        let successCount = 0;
        
        for (const productId of productIds) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            const response = await fetch(`/decks/${deckId}/cards`, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) successCount++;
        }
        
        closeDeckModal();
        clearSelection();
        alert(`${successCount} card(s) added to deck!`);
    } catch (error) {
        console.error('Error adding to deck:', error);
        alert('Failed to add cards. Please try again.');
    }
}

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
    div.className = 'bg-[#1a1a19] border border-white/10 rounded-lg hover:border-white/30 hover:shadow-xl transition overflow-hidden group relative';

    // Usa immagine HD se disponibile, altrimenti fallback su standard
    const imageUrl = card.hd_image_url || card.image_url || 'https://via.placeholder.com/245x342/1a1a19/666?text=No+Image';
    const hasHdImage = !!card.hd_image_url;

    div.innerHTML = `
        <!-- Checkbox -->
        <div class="absolute top-2 left-2 z-10">
            <input type="checkbox" 
                   class="card-checkbox w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded cursor-pointer" 
                   data-product-id="${card.product_id}"
                   onclick="event.stopPropagation(); toggleCardSelection(${card.product_id}, this)">
        </div>
        
        <!-- Quick Actions -->
        <div class="absolute top-2 right-2 z-10 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button onclick="event.stopPropagation(); quickAddToCollection(${card.product_id})" 
                    class="p-1.5 bg-green-600/90 hover:bg-green-600 rounded text-white transition" 
                    title="Add to Collection">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button onclick="event.stopPropagation(); quickAddToDeck(${card.product_id})" 
                    class="p-1.5 bg-blue-600/90 hover:bg-blue-600 rounded text-white transition" 
                    title="Add to Deck">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </button>
        </div>
        
        ${hasHdImage ? `
        <!-- HD Badge -->
        <div class="absolute top-2 right-2 z-[5] opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
            <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-blue-500/90 text-white rounded">
                HD
            </span>
        </div>
        ` : ''}
        
        <div class="aspect-[245/342] bg-black/50 overflow-hidden cursor-pointer" onclick="window.location.href='/tcg/cards/${card.product_id}'">
            <img 
                src="${escapeHtml(imageUrl)}" 
                alt="${escapeHtml(card.name)}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                loading="lazy"
                onerror="this.src='${escapeHtml(card.image_url || 'https://via.placeholder.com/245x342/1a1a19/666?text=No+Image')}'"
            >
        </div>
        <div class="p-2 cursor-pointer" onclick="window.location.href='/tcg/cards/${card.product_id}'">
            <h3 class="text-xs font-semibold text-white truncate group-hover:text-blue-400 transition">
                ${escapeHtml(card.name)}
            </h3>
            <div class="flex items-center justify-between mt-0.5">
                ${card.card_number ? `<p class="text-xs text-gray-400">#${escapeHtml(card.card_number)}</p>` : '<span></span>'}
                ${card.hp ? `<span class="text-xs text-red-400 font-semibold">${escapeHtml(card.hp)} HP</span>` : ''}
            </div>
        </div>
    `;

    return div;
}

// Quick actions for single card
function quickAddToCollection(productId) {
    selectedCards.clear();
    selectedCards.add(productId);
    openBulkCollectionModal();
}

function quickAddToDeck(productId) {
    selectedCards.clear();
    selectedCards.add(productId);
    openBulkDeckModal();
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
