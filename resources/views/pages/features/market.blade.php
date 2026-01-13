<!-- SECTION 2: Live Market Intelligence -->
<div class="py-20 bg-[#0a0a0a]">
    <div class="container mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Visual placeholder -->
            <div class="bg-gradient-to-br from-green-600/20 to-emerald-600/20 border border-white/10 rounded-2xl p-8 aspect-square flex items-center justify-center order-2 lg:order-1">
                <svg class="w-48 h-48 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                </svg>
            </div>
            
            <!-- Content -->
            <div class="order-1 lg:order-2">
                <div class="inline-block px-4 py-2 bg-green-600/20 rounded-full text-green-400 text-sm font-semibold mb-4">
                    {{ __('features/market.badge') }}
                </div>
                <h2 class="text-4xl font-bold mb-6">{{ __('features/market.title') }}</h2>
                <p class="text-xl text-gray-400 mb-8">{{ __('features/market.subtitle') }}</p>
                
                <div class="space-y-4">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4'] as $feat)
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-400 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold mb-1">{{ __('features/market.' . $feat . '_title') }}</h4>
                            <p class="text-gray-400">{{ __('features/market.' . $feat . '_text') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <p class="text-2xl font-bold text-green-400 mt-8">{{ __('features/market.tagline') }}</p>
                
                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="bg-green-600/10 border border-green-600/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-400">127K+</div>
                        <div class="text-sm text-gray-400">{{ __('features/market.stat1') }}</div>
                    </div>
                    <div class="bg-green-600/10 border border-green-600/20 rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-400">3.2M+</div>
                        <div class="text-sm text-gray-400">{{ __('features/market.stat2') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
