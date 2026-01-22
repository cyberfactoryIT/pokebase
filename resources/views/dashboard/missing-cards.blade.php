<!-- Set Completion Section -->
<div class="bg-white/5 border border-white/10 rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="font-semibold text-xl text-white flex items-center gap-3">
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            {{ __('dashboard.set_completion') }}
        </h3>
        <a href="{{ route('collection.index') }}" class="text-purple-400 hover:text-purple-300 text-sm font-medium flex items-center gap-1 transition">
            {{ __('dashboard.view_all_sets') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Popular Sets List -->
        <div>
            <div id="popularSetsLoading" class="text-center py-12">
                <svg class="animate-spin h-8 w-8 text-purple-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-400 text-sm">{{ __('dashboard.loading') }}...</p>
            </div>
            
            <div id="popularSetsList" class="hidden space-y-3 max-h-[500px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                <!-- Sets will be loaded here -->
            </div>
        </div>
        
        <!-- Right: Missing Cards Carousel -->
        <div id="missingCardsSection" class="hidden">
            <div class="bg-white/5 border border-white/10 rounded-lg p-4 mb-4">
                <h4 id="selectedSetName" class="text-white font-semibold mb-2"></h4>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400">{{ __('dashboard.missing') }}: <span id="missingCount" class="text-white font-medium"></span></span>
                    <span class="text-gray-400">{{ __('dashboard.owned') }}: <span id="ownedCount" class="text-white font-medium"></span></span>
                </div>
            </div>
            
            <div class="relative">
                <!-- Carousel Container -->
                <div id="missingCardsCarousel" class="grid grid-cols-3 gap-3 mb-4">
                    <!-- Cards will be loaded here (3 at a time) -->
                </div>
                
                <!-- Navigation -->
                <div class="flex items-center justify-between">
                    <button id="carouselPrev" class="bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg px-4 py-2 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <span class="text-sm text-white">{{ __('dashboard.previous') }}</span>
                    </button>
                    
                    <span id="carouselPage" class="text-sm text-gray-400"></span>
                    
                    <button id="carouselNext" class="bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg px-4 py-2 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="text-sm text-white">{{ __('dashboard.next') }}</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Right: Empty State -->
        <div id="emptyState" class="flex items-center justify-center text-gray-500 text-sm">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p>{{ __('dashboard.select_set_to_view_missing') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allMissingCards = [];
    let currentPage = 0;
    const cardsPerPage = 3;
    
    // Load popular sets on page load
    loadPopularSets();
    
    function loadPopularSets() {
        fetch('/api/user/popular-sets')
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                document.getElementById('popularSetsLoading').classList.add('hidden');
                document.getElementById('popularSetsList').classList.remove('hidden');
                
                const container = document.getElementById('popularSetsList');
                
                if (!data.sets || data.sets.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-12 text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-sm">{{ __('dashboard.no_sets_in_collection') }}</p>
                        </div>
                    `;
                    return;
                }
                
                container.innerHTML = data.sets.map(set => `
                    <div class="set-item bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg p-4 cursor-pointer transition"
                         data-set-id="${set.group_id}"
                         data-set-name="${escapeHtml(set.name)}">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-white font-medium text-sm">${escapeHtml(set.name)}</h5>
                            <span class="text-xs ${set.completion >= 100 ? 'text-green-400' : 'text-purple-400'} font-bold">
                                ${set.completion}%
                            </span>
                        </div>
                        <div class="h-2 bg-white/5 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-gradient-to-r ${set.completion >= 100 ? 'from-green-600 to-emerald-600' : 'from-purple-600 to-pink-600'} transition-all" 
                                 style="width: ${set.completion}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <span>${set.owned}/${set.total} {{ __('dashboard.cards') }}</span>
                            ${set.completion < 100 ? `<span class="text-orange-400">${set.total - set.owned} {{ __('dashboard.missing') }}</span>` : `<span class="text-green-400">✓ {{ __('dashboard.complete') }}</span>`}
                        </div>
                    </div>
                `).join('');
                
                // Add click handlers
                container.querySelectorAll('.set-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const setId = this.dataset.setId;
                        const setName = this.dataset.setName;
                        
                        // Highlight selected
                        container.querySelectorAll('.set-item').forEach(i => i.classList.remove('ring-2', 'ring-purple-500'));
                        this.classList.add('ring-2', 'ring-purple-500');
                        
                        loadMissingCards(setId, setName);
                    });
                });
            })
            .catch(err => {
                console.error('Error loading sets:', err);
                document.getElementById('popularSetsLoading').classList.add('hidden');
                document.getElementById('popularSetsList').classList.remove('hidden');
                document.getElementById('popularSetsList').innerHTML = `
                    <div class="text-center py-12 text-red-400">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">{{ __('dashboard.error_loading') }}</p>
                    </div>
                `;
            });
    }
    
    function loadMissingCards(setId, setName) {
        document.getElementById('emptyState').classList.add('hidden');
        document.getElementById('missingCardsSection').classList.remove('hidden');
        document.getElementById('selectedSetName').textContent = setName;
        
        const carousel = document.getElementById('missingCardsCarousel');
        carousel.innerHTML = `
            <div class="col-span-3 text-center py-8">
                <svg class="animate-spin h-8 w-8 text-purple-400 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;
        
        fetch(`/api/expansions/${setId}/missing-cards`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                // Validate data structure
                if (!data || typeof data !== 'object') {
                    throw new Error('Invalid response format');
                }
                
                allMissingCards = data.missing_cards || [];
                currentPage = 0;
                
                const missingCount = (data.total_count || 0) - (data.owned_count || 0);
                const ownedCount = data.owned_count || 0;
                
                document.getElementById('missingCount').textContent = missingCount;
                document.getElementById('ownedCount').textContent = ownedCount;
                
                if (allMissingCards.length === 0) {
                    carousel.innerHTML = `
                        <div class="col-span-3 text-center py-8 text-green-400">
                            <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="font-semibold">{{ __('dashboard.set_complete') }}!</p>
                        </div>
                    `;
                    document.getElementById('carouselPrev').disabled = true;
                    document.getElementById('carouselNext').disabled = true;
                    document.getElementById('carouselPage').textContent = '';
                } else {
                    renderCarousel();
                }
            })
            .catch(err => {
                console.error('Error loading missing cards:', err);
                carousel.innerHTML = `
                    <div class="col-span-3 text-center py-8 text-red-400">
                        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">{{ __('dashboard.error_loading') }}</p>
                        <p class="text-xs text-gray-500 mt-2">${err.message}</p>
                    </div>
                `;
                document.getElementById('carouselPrev').disabled = true;
                document.getElementById('carouselNext').disabled = true;
                document.getElementById('carouselPage').textContent = '';
            });
    }
    
    function renderCarousel() {
        const startIdx = currentPage * cardsPerPage;
        const endIdx = startIdx + cardsPerPage;
        const cardsToShow = allMissingCards.slice(startIdx, endIdx);
        const totalPages = Math.ceil(allMissingCards.length / cardsPerPage);
        
        const carousel = document.getElementById('missingCardsCarousel');
        carousel.innerHTML = cardsToShow.map(card => `
            <a href="/tcg/cards/${card.id}" class="block group">
                <div class="bg-white/5 hover:bg-white/10 border border-white/10 hover:border-purple-500/50 rounded-lg overflow-hidden transition">
                    <div class="aspect-[2/3] bg-gradient-to-br from-white/5 to-white/0 flex items-center justify-center p-2">
                        ${card.image_url ? 
                            `<img src="${card.image_url}" alt="${escapeHtml(card.name)}" class="w-full h-full object-contain group-hover:scale-105 transition-transform">` :
                            `<svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>`
                        }
                    </div>
                    <div class="p-2">
                        <p class="text-white text-xs font-medium truncate">${escapeHtml(card.name)}</p>
                        ${card.rarity ? `<p class="text-gray-400 text-xs truncate">${escapeHtml(card.rarity)}</p>` : ''}
                    </div>
                </div>
            </a>
        `).join('');
        
        // Update pagination
        document.getElementById('carouselPage').textContent = `${currentPage + 1} / ${totalPages}`;
        document.getElementById('carouselPrev').disabled = currentPage === 0;
        document.getElementById('carouselNext').disabled = currentPage >= totalPages - 1;
    }
    
    // Navigation handlers
    document.getElementById('carouselPrev').addEventListener('click', function() {
        if (currentPage > 0) {
            currentPage--;
            renderCarousel();
        }
    });
    
    document.getElementById('carouselNext').addEventListener('click', function() {
        const totalPages = Math.ceil(allMissingCards.length / cardsPerPage);
        if (currentPage < totalPages - 1) {
            currentPage++;
            renderCarousel();
        }
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@if($userExpansions && $userExpansions->isNotEmpty())
<div class="bg-white/5 border border-white/10 rounded-xl p-6">
    <div class="mb-6">
        <h3 class="font-semibold text-xl text-white flex items-center gap-3 mb-4">
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            {{ __('dashboard.missing_cards_to_complete') }}
        </h3>
        
        <!-- Expansion Selector -->
        <select id="expansionSelect" 
                class="w-full bg-white/5 border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition cursor-pointer">
            <option value="">{{ __('dashboard.select_expansion') }}</option>
            @foreach($userExpansions as $expansion)
            <option value="{{ $expansion->id }}" data-name="{{ $expansion->name }}">
                {{ $expansion->name }}
            </option>
            @endforeach
        </select>
    </div>
    
    <!-- Loading State -->
    <div id="missingCardsLoading" class="hidden text-center py-12">
        <svg class="animate-spin h-12 w-12 text-purple-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-400">{{ __('dashboard.loading_missing_cards') }}...</p>
    </div>
    
    <!-- Empty State -->
    <div id="missingCardsEmpty" class="text-center py-12 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p>{{ __('dashboard.select_expansion_to_view_missing') }}</p>
    </div>
    
    <!-- Progress Bar -->
    <div id="missingCardsProgress" class="hidden mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-400">{{ __('dashboard.completion_progress') }}</span>
            <span id="progressPercentage" class="text-sm font-bold text-purple-400">0%</span>
        </div>
        <div class="h-3 bg-white/5 rounded-full overflow-hidden border border-white/10">
            <div id="progressBar" class="h-full bg-gradient-to-r from-purple-600 to-pink-600 transition-all duration-500" style="width: 0%"></div>
        </div>
        <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
            <span id="progressOwned">0 {{ __('dashboard.owned') }}</span>
            <span id="progressTotal">0 {{ __('dashboard.total_cards') }}</span>
        </div>
    </div>
    
    <!-- Missing Cards List -->
    <div id="missingCardsList" class="hidden space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
        <!-- Cards will be dynamically inserted here -->
    </div>
    
    <!-- No Missing Cards (Complete Set) -->
    <div id="missingCardsComplete" class="hidden text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-600 to-emerald-600 rounded-full mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h4 class="text-lg font-semibold text-white mb-2">{{ __('dashboard.set_complete') }}!</h4>
        <p class="text-gray-400">{{ __('dashboard.you_own_all_cards') }}</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const expansionSelect = document.getElementById('expansionSelect');
    const loadingDiv = document.getElementById('missingCardsLoading');
    const emptyDiv = document.getElementById('missingCardsEmpty');
    const progressDiv = document.getElementById('missingCardsProgress');
    const listDiv = document.getElementById('missingCardsList');
    const completeDiv = document.getElementById('missingCardsComplete');
    const progressBar = document.getElementById('progressBar');
    const progressPercentage = document.getElementById('progressPercentage');
    const progressOwned = document.getElementById('progressOwned');
    const progressTotal = document.getElementById('progressTotal');
    
    // Handle expansion selection
    expansionSelect.addEventListener('change', async function() {
        const expansionId = this.value;
        
        // Hide all sections
        emptyDiv.classList.add('hidden');
        progressDiv.classList.add('hidden');
        listDiv.classList.add('hidden');
        completeDiv.classList.add('hidden');
        
        if (!expansionId) {
            emptyDiv.classList.remove('hidden');
            return;
        }
        
        // Show loading
        loadingDiv.classList.remove('hidden');
        
        try {
            const response = await fetch(`/api/expansions/${expansionId}/missing-cards`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Failed to fetch');
            
            const data = await response.json();
            
            // Hide loading
            loadingDiv.classList.add('hidden');
            
            // Update progress bar
            const percentage = data.completion_percentage || 0;
            progressBar.style.width = `${percentage}%`;
            progressPercentage.textContent = `${Math.round(percentage)}%`;
            progressOwned.textContent = `${data.owned_count} {{ __('dashboard.owned') }}`;
            progressTotal.textContent = `${data.total_count} {{ __('dashboard.total_cards') }}`;
            progressDiv.classList.remove('hidden');
            
            // Check if set is complete
            if (data.missing_cards.length === 0) {
                completeDiv.classList.remove('hidden');
                return;
            }
            
            // Render missing cards
            listDiv.innerHTML = '';
            data.missing_cards.forEach(card => {
                const cardElement = createMissingCardElement(card);
                listDiv.appendChild(cardElement);
            });
            listDiv.classList.remove('hidden');
            
        } catch (error) {
            console.error('Error fetching missing cards:', error);
            loadingDiv.classList.add('hidden');
            emptyDiv.classList.remove('hidden');
            emptyDiv.innerHTML = `
                <svg class="w-16 h-16 mx-auto mb-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-400">{{ __('dashboard.error_loading_missing_cards') }}</p>
            `;
        }
    });
    
    // Create missing card element
    function createMissingCardElement(card) {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-4 p-3 bg-white/5 border border-white/10 rounded-lg hover:border-purple-500/50 transition';
        
        // Rarity color mapping
        const rarityColors = {
            'common': 'bg-gray-500',
            'uncommon': 'bg-green-500',
            'rare': 'bg-blue-500',
            'ultra rare': 'bg-purple-500',
            'secret rare': 'bg-pink-500',
            'legendary': 'bg-yellow-500'
        };
        const rarityClass = rarityColors[card.rarity?.toLowerCase()] || 'bg-gray-500';
        
        div.innerHTML = `
            <!-- Card Image Thumbnail -->
            <div class="flex-shrink-0 w-12 h-16 bg-gradient-to-br from-gray-800 to-gray-900 rounded border border-white/10 overflow-hidden">
                ${card.image_url 
                    ? `<img src="${card.image_url}" alt="${card.name}" class="w-full h-full object-cover" loading="lazy">`
                    : `<div class="flex items-center justify-center h-full text-gray-600">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                         </svg>
                       </div>`
                }
            </div>
            
            <!-- Card Info -->
            <div class="flex-grow min-w-0">
                <h4 class="font-semibold text-white truncate">${card.name}</h4>
                <div class="flex items-center gap-2 mt-1">
                    ${card.number ? `<span class="text-xs text-gray-500">#${card.number}</span>` : ''}
                    ${card.rarity ? `<span class="text-xs px-2 py-0.5 ${rarityClass} text-white rounded">${card.rarity}</span>` : ''}
                </div>
            </div>
            
            <!-- Quick Add Button -->
            <button onclick="quickAddMissingCard(${card.id})" 
                    class="flex-shrink-0 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white text-sm font-semibold rounded-lg transition">
                {{ __('dashboard.add') }}
            </button>
        `;
        
        return div;
    }
});

// Quick add function for missing cards
function quickAddMissingCard(cardId) {
    // Pre-fill the quick add form with this card
    const quickAddForm = document.querySelector('#quickAddForm');
    if (quickAddForm) {
        const cardInput = quickAddForm.querySelector('#selectedCardId');
        if (cardInput) {
            cardInput.value = cardId;
            // Optionally scroll to the form
            quickAddForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            // Submit the form
            quickAddForm.dispatchEvent(new Event('submit'));
        }
    }
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(147, 51, 234, 0.5);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(147, 51, 234, 0.7);
}
</style>
@endif
