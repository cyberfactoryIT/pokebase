<!-- Missing Cards Section - TCGDEX -->
@if($userExpansions->isNotEmpty())
<div class="bg-gradient-to-br from-purple-900/20 to-pink-900/20 border border-white/10 rounded-xl p-6">
    <div class="flex items-center gap-3 mb-6">
        <div class="bg-purple-500/20 p-2 rounded-lg">
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-white">{{ __('dashboard.missing_cards') }}</h3>
    </div>

    <div class="space-y-4">
        <!-- Expansion Selector -->
        <select id="expansionSelect" 
                class="w-full bg-white/5 border border-white/10 text-white rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition cursor-pointer">
            <option value="">{{ __('dashboard.select_expansion') }}</option>
            @foreach($userExpansions as $expansion)
            @php
                $expansionName = is_array($expansion->name) ? ($expansion->name['en'] ?? $expansion->tcgdex_id) : $expansion->name;
            @endphp
            <option value="{{ $expansion->id }}" data-name="{{ $expansionName }}" data-tcgdex-id="{{ $expansion->tcgdex_id }}">
                {{ $expansionName }}
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
    
    <!-- Missing Cards Results -->
    <div id="missingCardsResults" class="hidden mt-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-gray-400">
                    <span id="missingCount" class="text-white font-semibold">0</span> {{ __('dashboard.missing_cards') }}
                    <span id="missingValue" class="text-purple-400 font-semibold ml-2"></span>
                </p>
                <p class="text-gray-400 text-sm mt-1">
                    <span class="text-gray-500">{{ __('dashboard.completion_progress') }}:</span>
                    <span id="completionPercentage" class="text-white font-medium">0%</span>
                </p>
            </div>
        </div>
        
        <!-- Horizontal Scrollable Card List -->
        <div class="relative">
            <div id="missingCardsGrid" class="flex gap-4 overflow-x-auto pb-4 scroll-smooth scrollbar-hide">
                <!-- Cards will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Empty State -->
    <div id="missingCardsEmpty" class="hidden text-center py-12">
        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-gray-400">{{ __('dashboard.no_missing_cards') }}</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const expansionSelect = document.getElementById('expansionSelect');
    const loadingState = document.getElementById('missingCardsLoading');
    const resultsContainer = document.getElementById('missingCardsResults');
    const emptyState = document.getElementById('missingCardsEmpty');
    const missingCardsGrid = document.getElementById('missingCardsGrid');
    const missingCount = document.getElementById('missingCount');
    
    if (!expansionSelect) return;
    
    expansionSelect.addEventListener('change', async function() {
        const setId = this.value;
        const tcgdexId = this.options[this.selectedIndex]?.dataset.tcgdexId;
        
        if (!setId || !tcgdexId) {
            resultsContainer.classList.add('hidden');
            emptyState.classList.add('hidden');
            return;
        }
        
        // Show loading
        loadingState.classList.remove('hidden');
        resultsContainer.classList.add('hidden');
        emptyState.classList.add('hidden');
        
        try {
            // Fetch missing cards for TCGDEX set
            const response = await fetch(`/api/pokemon/sets/${tcgdexId}/missing`);
            const data = await response.json();
            
            loadingState.classList.add('hidden');
            
            if (data.missing && data.missing.length > 0) {
                const missingCountEl = document.getElementById('missingCount');
                const missingValueEl = document.getElementById('missingValue');
                const completionPercentage = document.getElementById('completionPercentage');
                
                missingCountEl.textContent = data.missing.length;
                
                // Display total missing value
                if (missingValueEl && data.total_missing_value_eur !== undefined) {
                    const userCurrency = '{{ auth()->user()->preferred_currency ?? "DKK" }}';
                    const exchangeRate = parseFloat('{{ config("app.exchange_rates.EUR_to_" . (auth()->user()->preferred_currency ?? "DKK")) ?? 7.45 }}');
                    const valueInUserCurrency = data.total_missing_value_eur * exchangeRate;
                    
                    missingValueEl.textContent = `(${valueInUserCurrency.toFixed(2)} ${userCurrency})`;
                }
                
                if (completionPercentage && data.completion_percentage !== undefined) {
                    completionPercentage.textContent = data.completion_percentage + '%';
                }
                
                missingCardsGrid.innerHTML = data.missing.map(card => {
                    // Handle JSON name field
                    let cardName = 'Unknown';
                    if (card.name) {
                        if (typeof card.name === 'object' && card.name.en) {
                            cardName = card.name.en;
                        } else if (typeof card.name === 'string') {
                            cardName = card.name;
                        }
                    }
                    
                    return `
                        <a href="/pokemon/cards/${card.tcgdex_id}" 
                           class="group relative bg-white/5 hover:bg-white/10 border border-white/10 hover:border-purple-500/50 rounded-lg overflow-hidden transition-all flex-shrink-0 w-40">
                            <div class="aspect-[2/3] bg-gradient-to-br from-gray-800 to-gray-900">
                                <img src="${card.image_small_url}/low.webp" 
                                     alt="${cardName}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy"
                                     onerror="this.src='/images/card-placeholder.png'">
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/70 to-transparent p-2">
                                <p class="text-white text-xs font-medium truncate">${cardName}</p>
                                <p class="text-gray-400 text-xs">#${card.local_id}</p>
                            </div>
                        </a>
                    `;
                }).join('');
                resultsContainer.classList.remove('hidden');
            } else {
                emptyState.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error loading missing cards:', error);
            loadingState.classList.add('hidden');
            emptyState.classList.remove('hidden');
        }
    });
});
</script>
@endif
