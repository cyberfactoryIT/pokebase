<!-- SECTION 3: Deck Builder Pro -->
<div class="py-20 bg-black">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <div class="inline-block px-4 py-2 bg-purple-600/20 rounded-full text-purple-400 text-sm font-semibold mb-4">
                {{ __('features/deckbuilder.badge') }}
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-6">{{ __('features/deckbuilder.title') }}</h2>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">{{ __('features/deckbuilder.subtitle') }}</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            @foreach(['feat1', 'feat2', 'feat3', 'feat4', 'feat5', 'feat6'] as $feat)
            <div class="bg-[#161615] border border-white/10 rounded-xl p-6">
                <div class="w-12 h-12 bg-purple-600/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <h4 class="font-bold text-lg mb-2">{{ __('features/deckbuilder.' . $feat . '_title') }}</h4>
                <p class="text-gray-400">{{ __('features/deckbuilder.' . $feat . '_text') }}</p>
            </div>
            @endforeach
        </div>
        
        <p class="text-3xl font-bold text-purple-400 text-center">{{ __('features/deckbuilder.tagline') }}</p>
    </div>
</div>
