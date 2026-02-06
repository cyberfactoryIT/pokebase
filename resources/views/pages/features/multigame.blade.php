<!-- SECTION 5: Multi-Game Universe -->
<div class="py-20 bg-black">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <div class="inline-block px-4 py-2 bg-pink-600/20 rounded-full text-pink-400 text-sm font-semibold mb-4">
                {{ __('features/multigame.badge') }}
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-6">{{ __('features/multigame.title') }}</h2>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">{{ __('features/multigame.subtitle') }}</p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto mb-12">
            <!-- Pokémon -->
            <div class="bg-gradient-to-br from-red-600/10 to-yellow-600/10 border border-red-600/20 rounded-2xl p-8 hover:border-red-600/40 transition">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">⚡</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Pokémon TCG</h3>
                </div>
                <ul class="space-y-3 text-gray-400">
                    @foreach(['feat1', 'feat2', 'feat3'] as $feat)
                    <li class="flex items-start">
                        <span class="text-red-400 mr-2">•</span>
                        {{ __('features/multigame.pokemon_' . $feat) }}
                    </li>
                    @endforeach
                </ul>
            </div>
            
            <!-- Lorcana -->
            <div class="bg-gradient-to-br from-blue-600/10 to-purple-600/10 border border-blue-600/20 rounded-2xl p-8 hover:border-blue-600/40 transition">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">✨</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Disney Lorcana</h3>
                </div>
                <ul class="space-y-3 text-gray-400">
                    @foreach(['feat1', 'feat2', 'feat3'] as $feat)
                    <li class="flex items-start">
                        <span class="text-blue-400 mr-2">•</span>
                        {{ __('features/multigame.lorcana_' . $feat) }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <!-- Coming soon -->
        <div class="text-center bg-gradient-to-r from-pink-600/10 to-purple-600/10 border border-pink-600/20 rounded-2xl p-8">
            <p class="text-gray-400 mb-4 text-lg">{{ __('features/multigame.coming_label') }}</p>
            <p class="text-gray-500 text-sm">{{ __('features/multigame.coming_more') }}</p>
        </div>
        
        <p class="text-3xl font-bold text-pink-400 text-center mt-12">{{ __('features/multigame.tagline') }}</p>
    </div>
</div>
