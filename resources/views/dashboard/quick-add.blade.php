<!-- Quick Add Card Section -->
<div class="bg-gradient-to-br from-blue-900/20 to-purple-900/20 border border-white/10 rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="bg-blue-500/20 p-2 rounded-lg">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-white">{{ __('dashboard.quick_add_card') }}</h3>
    </div>

    <form id="quick-add-form" action="{{ route('collection.quick-add') }}" method="POST" class="space-y-4">
        @csrf
        
        <!-- Card Search -->
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
                {{ __('dashboard.search_card') }}
            </label>
            <div class="relative">
                <input 
                    type="text" 
                    id="card-search" 
                    name="card_name"
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
            <input type="hidden" id="selected-card-id" name="card_id">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    {{ __('dashboard.quantity') }}
                </label>
                <input 
                    type="number" 
                    name="quantity" 
                    value="1" 
                    min="1" 
                    max="100"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                >
            </div>

            <!-- Condition -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    {{ __('dashboard.condition') }}
                </label>
                <select 
                    name="condition"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                >
                    <option value="M">M - {{ __('dashboard.condition_mint') }}</option>
                    <option value="NM" selected>NM - {{ __('dashboard.condition_near_mint') }}</option>
                    <option value="LP">LP - {{ __('dashboard.condition_lightly_played') }}</option>
                    <option value="MP">MP - {{ __('dashboard.condition_moderately_played') }}</option>
                    <option value="HP">HP - {{ __('dashboard.condition_heavily_played') }}</option>
                    <option value="D">D - {{ __('dashboard.condition_damaged') }}</option>
                </select>
            </div>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2 group"
        >
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>{{ __('dashboard.add_to_collection') }}</span>
        </button>
    </form>

    <!-- Success/Error Messages -->
    <div id="quick-add-message" class="hidden mt-4"></div>
</div>
