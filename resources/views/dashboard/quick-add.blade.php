<!-- Quick Card Search Section -->
<div class="bg-gradient-to-br from-blue-900/20 to-purple-900/20 border border-white/10 rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="bg-blue-500/20 p-2 rounded-lg">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-white">{{ __('dashboard.quick_search_card') }}</h3>
    </div>

    <div data-catalog-backend="{{ $catalogBackend ?? 'tcgcsv' }}" data-game-slug="{{ $currentGame->slug ?? 'pokemon' }}">
        <!-- Card Search -->
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
                {{ __('dashboard.search_card') }}
            </label>
            <div class="relative">
                <input 
                    type="text" 
                    id="card-search" 
                    class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 pr-10 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                    placeholder="{{ __('dashboard.type_card_name') }}"
                    autocomplete="off"
                >
                <!-- Search Icon -->
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div id="search-results" class="hidden absolute z-50 w-full mt-2 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto"></div>
            </div>
        </div>

        <p class="text-sm text-gray-400 mt-3">
            {{ __('dashboard.search_hint') }}
        </p>
    </div>
</div>
