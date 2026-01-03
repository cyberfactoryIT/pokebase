<!-- Prices Hidden - Upgrade Required -->
<div class="bg-gray-900/50 border border-white/10 rounded-lg py-8 px-6 text-center mb-8">
    <div class="mb-4">
        <svg class="mx-auto h-16 w-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
    </div>
    <h3 class="text-xl font-bold text-white mb-2">{{ __('prices.hidden.title') }}</h3>
    <p class="text-gray-400 mb-6 max-w-md mx-auto">{{ __('prices.hidden.body') }}</p>
    
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('billing.index') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            {{ __('prices.hidden.cta_upgrade') }}
        </a>
        <span class="text-gray-500 self-center">{{ __('prices.hidden.or') }}</span>
        <a href="{{ route('deck-evaluation.packages.index') }}" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-semibold flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            {{ __('prices.hidden.cta_deck_evaluation') }}
        </a>
    </div>
</div>
