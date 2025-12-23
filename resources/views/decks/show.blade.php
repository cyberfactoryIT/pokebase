@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('decks.index') }}" class="text-gray-400 hover:text-white transition flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Decks
            </a>

            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $deck->name }}</h1>
                    <div class="flex items-center gap-4 text-gray-400">
                        @if($deck->format)
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded">{{ $deck->format }}</span>
                        @endif
                        <span>{{ $deck->totalCards() }} cards</span>
                        <span>Created {{ $deck->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <a href="{{ route('decks.edit', $deck) }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-gray-300 rounded-lg transition">
                    Edit Deck
                </a>
            </div>

            @if($deck->description)
            <p class="text-gray-400 mt-4">{{ $deck->description }}</p>
            @endif
        </div>

        @if(session('success'))
        <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4 mb-6">
            <p class="text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        <!-- Quick Add Cards - Two Columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Add from Collection -->
            <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Add from Collection</h2>
                <div class="relative" x-data="{ searchOpen: false }" @click.away="searchOpen = false">
                    <input 
                        type="text" 
                        id="deck-card-search" 
                        placeholder="Search your collection..."
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @focus="searchOpen = true"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div id="deck-search-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                        <!-- Results will be inserted here by JS -->
                    </div>
                </div>
            </div>

            <!-- Add from Catalog -->
            <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Add from Catalog</h2>
                <div class="relative" x-data="{ catalogSearchOpen: false }" @click.away="catalogSearchOpen = false">
                    <input 
                        type="text" 
                        id="catalog-card-search" 
                        placeholder="Search all cards..."
                        class="w-full px-4 py-3 pl-10 bg-black/50 border border-white/20 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        @focus="catalogSearchOpen = true"
                    >
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div id="catalog-search-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                        <!-- Results will be inserted here by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Deck Contents -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            @if($deck->deckCards->isEmpty())
            <!-- Empty Deck -->
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-white text-xl font-semibold mb-2">Empty Deck</h3>
                <p class="text-gray-400 mb-6">Start adding cards by searching for them using the search bar above</p>
                <a href="{{ route('tcg.expansions.index') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    Browse Cards
                </a>
            </div>
            @else
            <!-- Card List -->
            <div class="space-y-3">
                @foreach($deck->deckCards as $deckCard)
                @php
                    $card = $deckCard->card;
                    $inCollection = $card ? auth()->user()->collection()->where('product_id', $card->product_id)->exists() : false;
                @endphp
                @if($card)
                <div class="flex items-center gap-4 p-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition">
                    <div class="flex-shrink-0 w-16 h-16 bg-black/50 rounded flex items-center justify-center">
                        @if($card->image_url)
                        <img src="{{ $card->image_url }}" alt="{{ $card->name }}" class="w-full h-full object-cover rounded">
                        @else
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        @endif
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h4 class="text-white font-semibold">{{ $card->name }}</h4>
                            @if(!$inCollection)
                            <span class="px-2 py-0.5 bg-orange-500/20 border border-orange-500/50 text-orange-400 text-xs rounded-full whitespace-nowrap">
                                Not in Collection
                            </span>
                            <form method="POST" action="{{ route('collection.add') }}" class="inline" onsubmit="event.preventDefault(); quickAddCardToCollection({{ $card->product_id }}, '{{ addslashes($card->name) }}', this);">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $card->product_id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" 
                                    class="inline-flex items-center justify-center w-5 h-5 bg-green-600 hover:bg-green-700 text-white rounded-full transition-colors"
                                    title="Add to Collection">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                        <p class="text-gray-400 text-sm">
                            {{ $card->group->name ?? 'Unknown Set' }}
                            @if($card->number)
                            · #{{ $card->number }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Quantity -->
                        <form method="POST" action="{{ route('decks.cards.updateQuantity', [$deck, $deckCard]) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <label class="text-gray-400 text-sm">Qty:</label>
                            <input 
                                type="number" 
                                name="quantity" 
                                value="{{ $deckCard->quantity }}" 
                                min="1" 
                                max="4"
                                class="w-16 px-2 py-1 bg-black/50 border border-white/20 rounded text-white text-center"
                                onchange="this.form.submit()"
                            >
                        </form>

                        <!-- Remove -->
                        <form method="POST" action="{{ route('decks.cards.remove', [$deck, $deckCard]) }}" onsubmit="return confirm('Remove this card from the deck?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Deck card search from collection
const deckSearchInput = document.getElementById('deck-card-search');
const deckSearchDropdown = document.getElementById('deck-search-dropdown');
const catalogSearchInput = document.getElementById('catalog-card-search');
const catalogSearchDropdown = document.getElementById('catalog-search-dropdown');
const deckId = {{ $deck->id }};
let deckSearchDebounceTimer = null;
let catalogSearchDebounceTimer = null;
let currentDeckSearchRequest = 0;
let currentCatalogSearchRequest = 0;
let userCollectionProductIds = new Set();

// Load user collection IDs for checking
async function loadUserCollectionIds() {
    try {
        const response = await fetch('/collection/ids', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        userCollectionProductIds = new Set(data);
        console.log(`Loaded ${data.length} collection IDs`);
    } catch (error) {
        console.error('Error loading collection:', error);
    }
}

loadUserCollectionIds();

deckSearchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    
    clearTimeout(deckSearchDebounceTimer);
    
    if (query.length < 2) {
        deckSearchDropdown.classList.add('hidden');
        deckSearchDropdown.innerHTML = '';
        return;
    }
    
    deckSearchDebounceTimer = setTimeout(() => {
        searchCollectionCards(query);
    }, 300);
});

catalogSearchInput.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    
    clearTimeout(catalogSearchDebounceTimer);
    
    if (query.length < 2) {
        catalogSearchDropdown.classList.add('hidden');
        catalogSearchDropdown.innerHTML = '';
        return;
    }
    
    catalogSearchDebounceTimer = setTimeout(() => {
        searchCatalogCards(query);
    }, 300);
});

async function searchCollectionCards(query) {
    const requestId = ++currentDeckSearchRequest;
    
    try {
        // Search only in user's collection with limit
        const response = await fetch(`/api/search/cards?q=${encodeURIComponent(query)}&collection_only=1&limit=20`);
        
        if (requestId !== currentDeckSearchRequest) return;
        
        const data = await response.json();
        
        if (data.length === 0) {
            deckSearchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">No cards found in your collection</div>';
            deckSearchDropdown.classList.remove('hidden');
            return;
        }
        
        const resultsHTML = data.map(card => `
            <div class="px-4 py-3 hover:bg-white/10 cursor-pointer border-b border-white/10 last:border-b-0 flex items-center gap-3"
                 onclick="addCardToDeck(${card.product_id}, '${escapeHtml(card.name)}')">
                <div class="flex-shrink-0 w-12 h-16 bg-black/50 rounded overflow-hidden">
                    ${card.image_url ? `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-cover">` : ''}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white font-medium truncate">${escapeHtml(card.name)}</div>
                    <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) : ''}</div>
                </div>
                <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
        `).join('');
        
        deckSearchDropdown.innerHTML = resultsHTML;
        deckSearchDropdown.classList.remove('hidden');
    } catch (error) {
        console.error('Search error:', error);
    }
}

async function searchCatalogCards(query) {
    const requestId = ++currentCatalogSearchRequest;
    
    try {
        // Search all cards (no collection filter)
        const response = await fetch(`/api/search/cards?q=${encodeURIComponent(query)}`);
        
        if (requestId !== currentCatalogSearchRequest) return;
        
        const data = await response.json();
        
        if (data.length === 0) {
            catalogSearchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">No cards found</div>';
            catalogSearchDropdown.classList.remove('hidden');
            return;
        }
        
        const resultsHTML = data.map(card => {
            const inCollection = userCollectionProductIds.has(card.product_id);
            return `
                <div class="px-4 py-3 hover:bg-white/10 border-b border-white/10 last:border-b-0 flex items-center gap-3">
                    <div class="flex-shrink-0 w-12 h-16 bg-black/50 rounded overflow-hidden">
                        ${card.image_url ? `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-cover">` : ''}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <div class="text-white font-medium truncate">${escapeHtml(card.name)}</div>
                            ${!inCollection ? '<span class="text-orange-400 text-xs font-semibold whitespace-nowrap">(Not in Collection)</span>' : ''}
                        </div>
                        <div class="text-gray-400 text-sm">${escapeHtml(card.set_name || '')} ${card.card_number ? '· #' + escapeHtml(card.card_number) : ''}</div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        ${!inCollection ? `
                            <button onclick="event.stopPropagation(); quickAddToCollection(${card.product_id}, '${escapeHtml(card.name)}')" 
                                    class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition flex items-center gap-1"
                                    title="Add to Collection">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Collection
                            </button>
                        ` : ''}
                        <button onclick="addCardToDeck(${card.product_id}, '${escapeHtml(card.name)}')"
                                class="px-2 py-1 ${inCollection ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-600 hover:bg-orange-700'} text-white text-xs rounded transition flex items-center gap-1"
                                title="Add to Deck">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Deck
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        
        catalogSearchDropdown.innerHTML = resultsHTML;
        catalogSearchDropdown.classList.remove('hidden');
    } catch (error) {
        console.error('Search error:', error);
    }
}

async function quickAddToCollection(productId, cardName) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        
        const response = await fetch('{{ route("collection.add") }}', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            // Add to local collection set
            userCollectionProductIds.add(productId);
            // Refresh both dropdowns to update UI
            catalogSearchInput.dispatchEvent(new Event('input'));
            alert(`${cardName} added to collection!`);
        } else {
            alert('Failed to add card to collection');
        }
    } catch (error) {
        console.error('Error adding to collection:', error);
        alert('Failed to add card to collection');
    }
}

async function addCardToDeck(productId, cardName) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        
        const response = await fetch(`/decks/${deckId}/cards`, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            // Reload page to show updated deck
            window.location.reload();
        } else {
            alert('Failed to add card to deck');
        }
    } catch (error) {
        console.error('Error adding card:', error);
        alert('Failed to add card to deck');
    }
}

// Quick add to collection from deck list
async function quickAddCardToCollection(productId, cardName, form) {
    try {
        const response = await fetch('{{ route("collection.add") }}', {
            method: 'POST',
            body: new FormData(form)
        });
        
        if (response.ok) {
            userCollectionProductIds.add(productId);
            location.reload(); // Reload to update the badge
        } else {
            alert('Failed to add card to collection');
        }
    } catch (error) {
        console.error('Error adding to collection:', error);
        alert('Failed to add card to collection');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
