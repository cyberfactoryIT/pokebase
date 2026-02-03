@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Navigation Buttons -->
        <div class="mb-4 flex items-center gap-4">
            <!-- Back Button (browser history) -->
            <button onclick="window.history.back()" class="inline-flex items-center text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('catalog.back') }}
            </button>
            
            <!-- Separator -->
            <span class="text-gray-600">|</span>
            
            <!-- View Set Button -->
            <a href="{{ route('cmapi.sets.show', [$game, $card->set->cmapi_episode]) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300 transition">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                {{ __('catalog.view_set', ['set' => $card->set->name]) }}
            </a>
        </div>

        <!-- Card Detail Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Card Image & Details -->
            <div class="space-y-6">
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <div class="aspect-[245/342] max-w-md mx-auto">
                        @php
                            $imageUrl = $card->image_large_url ?? $card->image_small_url;
                        @endphp
                        @if($imageUrl)
                            <img 
                                src="{{ $imageUrl }}" 
                                alt="{{ $card->name }}"
                                class="w-full h-full object-contain rounded-lg shadow-lg"
                            >
                        @else
                            <div class="w-full h-full bg-black/50 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Basic Details -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Additional Info</h2>
                    
                    <dl class="space-y-3">
                        @if($card->number)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Card Number</dt>
                            <dd class="text-sm text-white">#{{ $card->number }}</dd>
                        </div>
                        @endif
                        
                        @if($card->rarity)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Rarity</dt>
                            <dd class="text-sm text-white">{{ $card->rarity }}</dd>
                        </div>
                        @endif
                        
                        @if($card->artist_name)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Artist</dt>
                            <dd class="text-sm text-white">{{ $card->artist_name }}</dd>
                        </div>
                        @endif
                        
                        @if($game === 'lorcana')
                            @if($card->card_type)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Card Type</dt>
                                <dd class="text-sm text-white">{{ $card->card_type }}</dd>
                            </div>
                            @endif
                            
                            @if($card->ink_cost !== null)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Ink Cost</dt>
                                <dd class="text-sm text-white">{{ $card->ink_cost }}</dd>
                            </div>
                            @endif
                            
                            @if($card->ink_color)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Ink Color</dt>
                                <dd class="text-sm text-white">{{ $card->ink_color }}</dd>
                            </div>
                            @endif
                            
                            @if($card->lore_value !== null)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Lore Value</dt>
                                <dd class="text-sm text-white">{{ $card->lore_value }}</dd>
                            </div>
                            @endif
                        @endif
                        
                        @if($game === 'onepiece')
                            @if($card->cost !== null)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Cost</dt>
                                <dd class="text-sm text-white">{{ $card->cost }}</dd>
                            </div>
                            @endif
                            
                            @if($card->power !== null)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Power</dt>
                                <dd class="text-sm text-white">{{ $card->power }}</dd>
                            </div>
                            @endif
                            
                            @if($card->counter !== null)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Counter</dt>
                                <dd class="text-sm text-white">{{ $card->counter }}</dd>
                            </div>
                            @endif
                            
                            @if($card->color)
                            <div class="flex justify-between py-2 border-b border-white/10">
                                <dt class="text-sm font-medium text-gray-400">Color</dt>
                                <dd class="text-sm text-white">{{ $card->color }}</dd>
                            </div>
                            @endif
                        @endif
                        
                        @if($card->hp !== null)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">HP</dt>
                            <dd class="text-sm text-white">{{ $card->hp }}</dd>
                        </div>
                        @endif
                        
                        
                    </dl>
                </div>
            </div>

            <!-- Right Column: Card Information & Actions -->
            <div class="space-y-6">
                <!-- Card Title & Badges -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h1 class="text-3xl font-bold text-white mb-4">{{ $card->name }}</h1>
                    
                    <!-- Info Badges -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if($card->set)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-700/50 border border-gray-600 rounded-full text-sm text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            {{ $card->set->name }}
                        </span>
                        @endif
                        
                        @if($card->number)
                        <span class="px-3 py-1.5 bg-gray-800/50 border border-gray-700 rounded-full text-sm text-gray-300">
                            #{{ $card->number }}
                        </span>
                        @endif
                        
                        @if($card->rarity)
                        <span class="px-3 py-1.5 bg-yellow-900/30 border border-yellow-700/50 rounded-full text-sm text-yellow-400 font-semibold">
                            {{ $card->rarity }}
                        </span>
                        @endif
                        
                        @if($game === 'lorcana')
                            @if($card->ink_color)
                            <span class="px-3 py-1.5 bg-blue-900/30 border border-blue-700/50 rounded-full text-sm text-blue-300">
                                {{ $card->ink_color }}
                            </span>
                            @endif
                            
                            @if($card->card_type)
                            <span class="px-3 py-1.5 bg-purple-900/30 border border-purple-700/50 rounded-full text-sm text-purple-300">
                                {{ $card->card_type }}
                            </span>
                            @endif
                            
                            @if($card->lore_value !== null)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-900/30 border border-indigo-700/50 rounded-full text-sm text-indigo-300 font-semibold">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                </svg>
                                {{ $card->lore_value }} Lore
                            </span>
                            @endif
                            
                            @if($card->ink_cost !== null)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-900/30 border border-cyan-700/50 rounded-full text-sm text-cyan-300 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                {{ $card->ink_cost }} Ink
                            </span>
                            @endif
                        @endif
                        
                        @if($game === 'onepiece')
                            @if($card->color)
                            <span class="px-3 py-1.5 bg-blue-900/30 border border-blue-700/50 rounded-full text-sm text-blue-300">
                                {{ $card->color }}
                            </span>
                            @endif
                            
                            @if($card->cost !== null)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-900/30 border border-orange-700/50 rounded-full text-sm text-orange-300 font-semibold">
                                {{ $card->cost }} Cost
                            </span>
                            @endif
                            
                            @if($card->power !== null)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-900/30 border border-red-700/50 rounded-full text-sm text-red-300 font-semibold">
                                {{ $card->power }} Power
                            </span>
                            @endif
                            
                            @if($card->counter !== null)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-900/30 border border-green-700/50 rounded-full text-sm text-green-300 font-semibold">
                                {{ $card->counter }} Counter
                            </span>
                            @endif
                        @endif
                    </div>
                    
                    @if($card->artist_name)
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-900/20 border border-amber-700/30 rounded-full">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                        <span class="text-sm text-amber-300 font-medium">{{ $card->artist_name }}</span>
                    </div>
                    @endif

                    <!-- Actions (for authenticated users) -->
                    @auth
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <div class="space-y-3">
                            <!-- Add to Collection -->
                            <form method="POST" action="{{ route('collection.add.cmapi') }}" class="w-full">
                                @csrf
                                <input type="hidden" name="cmapi_card_id" value="{{ $card->cmapi_id }}">
                                <button 
                                    type="submit"
                                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-lg flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add to Collection
                                </button>
                            </form>
                            
                            <!-- Interaction Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <button 
                                    id="likeBtn"
                                    onclick="toggleLike()"
                                    class="px-3 py-2 {{ $card->is_liked ? 'bg-red-600' : 'bg-white/10' }} hover:bg-red-700 text-white rounded-lg transition text-sm"
                                >
                                    ❤️ <span id="likeText">{{ $card->is_liked ? 'Unlike' : 'Like' }}</span>
                                </button>
                                <button 
                                    id="wishlistBtn"
                                    onclick="toggleWishlist()"
                                    class="px-3 py-2 {{ $card->is_in_wishlist ? 'bg-yellow-600' : 'bg-white/10' }} hover:bg-yellow-700 text-white rounded-lg transition text-sm"
                                >
                                    ⭐ <span id="wishlistText">{{ $card->is_in_wishlist ? 'In Wishlist' : 'Wishlist' }}</span>
                                </button>
                                <button 
                                    id="watchBtn"
                                    onclick="toggleWatch()"
                                    class="px-3 py-2 {{ $card->is_watched ? 'bg-green-600' : 'bg-white/10' }} hover:bg-green-700 text-white rounded-lg transition text-sm"
                                >
                                    👁️ <span id="watchText">{{ $card->is_watched ? 'Watching' : 'Watch' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endauth
                
                    @guest
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <p class="text-gray-400 text-center">
                            <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 underline">Log in</a> 
                            to add this card to your collection
                        </p>
                    </div>
                    @endguest
                </div>

                <!-- Pricing Information -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white">Market Prices</h2>
                        @if($card->raw && isset($card->raw['links']['cardmarket']))
                        <a href="{{ $card->raw['links']['cardmarket'] }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-400 hover:text-blue-300 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            View on CardMarket
                        </a>
                        @endif
                    </div>
                    <!-- Price History Chart -->
                    @if($card->id)
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-white">Price History</h2>
                            
                            <!-- Filters -->
                            <div class="flex gap-2">
                                <select id="languageFilter" class="bg-gray-800 text-white text-sm rounded px-3 py-1.5 border border-gray-700">
                                    <option value="en">English</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                    <option value="es">Spanish</option>
                                    <option value="it">Italian</option>
                                </select>
                                <select id="daysFilter" class="bg-gray-800 text-white text-sm rounded px-3 py-1.5 border border-gray-700">
                                    <option value="7">7 days</option>
                                    <option value="30" selected>30 days</option>
                                    <option value="90">90 days</option>
                                    <option value="180">6 months</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="relative h-64">
                            <canvas id="priceChart"></canvas>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 text-center">Near Mint (NM) condition prices from CardMarket</p>
                        
                       
                    </div>
                    @endif

                    @include('cmapi.cards.partials.prices', ['card' => $card, 'size' => 'large'])
                    
                   
                </div>

                

                
            </div>
        </div>
    </div>
</div>

@auth
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const cardId = {{ $card->id }};
let priceChart = null;

async function loadPriceHistory(language = 'en', days = 30) {
    try {
        const response = await fetch(`/api/cmapi/cards/${cardId}/price-history?language=${language}&condition=NM&days=${days}`);
        const data = await response.json();
        
        if (data.length === 0) {
            document.getElementById('priceChart').parentElement.innerHTML = 
                '<p class="text-gray-400 text-center py-8">No price history available yet. Data will be collected starting from the next daily sync.</p>';
            return;
        }
        
        updateChart(data);
    } catch (error) {
        console.error('Failed to load price history:', error);
    }
}

function updateChart(data) {
    const ctx = document.getElementById('priceChart');
    
    if (priceChart) {
        priceChart.destroy();
    }
    
    priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => new Date(d.price_date).toLocaleDateString('en-GB', { month: 'short', day: 'numeric' })),
            datasets: [
                {
                    label: 'Price',
                    data: data.map(d => d.price_eur),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Trend',
                    data: data.map(d => d.price_trend_eur),
                    borderColor: 'rgb(234, 179, 8)',
                    borderDash: [5, 5],
                    tension: 0.3,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: 'rgb(156, 163, 175)',
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': €' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        color: 'rgb(156, 163, 175)',
                        callback: function(value) {
                            return '€' + value.toFixed(2);
                        }
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: 'rgb(156, 163, 175)'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });
}

// Event listeners for filters
document.getElementById('languageFilter').addEventListener('change', (e) => {
    const days = document.getElementById('daysFilter').value;
    loadPriceHistory(e.target.value, days);
});

document.getElementById('daysFilter').addEventListener('change', (e) => {
    const language = document.getElementById('languageFilter').value;
    loadPriceHistory(language, e.target.value);
});

// Load initial chart
loadPriceHistory();

function toggleLike() {
    const btn = document.getElementById('likeBtn');
    const text = document.getElementById('likeText');
    
    const url = '{{ route('cmapi.cards.like', ['game' => $gameSlug, 'cardId' => $card->id]) }}';
    console.log('Calling like endpoint:', url);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    console.log('CSRF token:', csrfToken);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.status === 'liked') {
            btn.classList.remove('bg-white/10');
            btn.classList.add('bg-red-600');
            text.textContent = 'Unlike';
        } else {
            btn.classList.remove('bg-red-600');
            btn.classList.add('bg-white/10');
            text.textContent = 'Like';
        }
        
        // Show success message
        if (data.message) {
            showNotification(data.message, 'success');
        }
    })
    .catch(error => {
        console.error('Like Error:', error);
        showNotification('Error updating like status', 'error');
    });
}

function toggleWishlist() {
    const btn = document.getElementById('wishlistBtn');
    const text = document.getElementById('wishlistText');
    
    const url = '{{ route('cmapi.cards.wishlist', ['game' => $gameSlug, 'cardId' => $card->id]) }}';
    console.log('Calling wishlist endpoint:', url);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    console.log('CSRF token:', csrfToken);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Wishlist response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Wishlist response data:', data);
        if (data.status === 'added') {
            btn.classList.remove('bg-white/10');
            btn.classList.add('bg-yellow-600');
            text.textContent = 'In Wishlist';
        } else {
            btn.classList.remove('bg-yellow-600');
            btn.classList.add('bg-white/10');
            text.textContent = 'Wishlist';
        }
        
        // Show success message
        if (data.message) {
            showNotification(data.message, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating wishlist status', 'error');
    });
}

function toggleWatch() {
    const btn = document.getElementById('watchBtn');
    const text = document.getElementById('watchText');
    
    const url = '{{ route('cmapi.cards.watch', ['game' => $gameSlug, 'cardId' => $card->id]) }}';
    console.log('Calling watch endpoint:', url);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    console.log('CSRF token:', csrfToken);
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Watch response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Watch response data:', data);
        if (data.status === 'watched') {
            btn.classList.remove('bg-white/10');
            btn.classList.add('bg-green-600');
            text.textContent = 'Watching';
        } else {
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-white/10');
            text.textContent = 'Watch';
        }
        
        // Show success message
        if (data.message) {
            showNotification(data.message, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating watch status', 'error');
    });
}

function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endauth
@endsection
