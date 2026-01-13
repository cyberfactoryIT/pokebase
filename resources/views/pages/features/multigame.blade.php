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
        
        <div class="grid md:grid-cols-3 gap-8 mb-12">
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
            
            <!-- Magic -->
            <div class="bg-gradient-to-br from-blue-600/10 to-purple-600/10 border border-blue-600/20 rounded-2xl p-8 hover:border-blue-600/40 transition">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">🔮</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Magic: The Gathering</h3>
                </div>
                <ul class="space-y-3 text-gray-400">
                    @foreach(['feat1', 'feat2', 'feat3'] as $feat)
                    <li class="flex items-start">
                        <span class="text-blue-400 mr-2">•</span>
                        {{ __('features/multigame.magic_' . $feat) }}
                    </li>
                    @endforeach
                </ul>
            </div>
            
            <!-- One Piece -->
            <div class="bg-gradient-to-br from-orange-600/10 to-red-600/10 border border-orange-600/20 rounded-2xl p-8 hover:border-orange-600/40 transition">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-orange-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">🏴‍☠️</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">One Piece TCG</h3>
                </div>
                <ul class="space-y-3 text-gray-400">
                    @foreach(['feat1', 'feat2', 'feat3'] as $feat)
                    <li class="flex items-start">
                        <span class="text-orange-400 mr-2">•</span>
                        {{ __('features/multigame.onepiece_' . $feat) }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <!-- Coming soon -->
        <div class="text-center bg-gradient-to-r from-pink-600/10 to-purple-600/10 border border-pink-600/20 rounded-2xl p-8">
            <p class="text-gray-400 mb-2">{{ __('features/multigame.coming_label') }}</p>
            <div class="flex flex-wrap justify-center gap-4 text-lg font-semibold">
                <span class="px-4 py-2 bg-white/5 rounded-lg">Yu-Gi-Oh!</span>
                <span class="px-4 py-2 bg-white/5 rounded-lg">Digimon</span>
                <span class="px-4 py-2 bg-white/5 rounded-lg">Disney Lorcana</span>
                <span class="px-4 py-2 bg-white/5 rounded-lg">{{ __('features/multigame.coming_more') }}</span>
            </div>
        </div>
        
        <p class="text-3xl font-bold text-pink-400 text-center mt-12">{{ __('features/multigame.tagline') }}</p>
    </div>
</div>
