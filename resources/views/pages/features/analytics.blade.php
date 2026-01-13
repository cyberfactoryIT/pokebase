<!-- SECTION 4: Portfolio Analytics -->
<div class="py-20 bg-[#0a0a0a]">
    <div class="container mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Content -->
            <div>
                <div class="inline-block px-4 py-2 bg-yellow-600/20 rounded-full text-yellow-400 text-sm font-semibold mb-4">
                    {{ __('features/analytics.badge') }}
                </div>
                <h2 class="text-4xl font-bold mb-6">{{ __('features/analytics.title') }}</h2>
                <p class="text-xl text-gray-400 mb-8">{{ __('features/analytics.subtitle') }}</p>
                
                <div class="space-y-4">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4', 'feat5'] as $feat)
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-400 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold mb-1">{{ __('features/analytics.' . $feat . '_title') }}</h4>
                            <p class="text-gray-400">{{ __('features/analytics.' . $feat . '_text') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <p class="text-2xl font-bold text-yellow-400 mt-8">{{ __('features/analytics.tagline') }}</p>
                
                <!-- Stats cards -->
                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div class="bg-yellow-600/10 border border-yellow-600/20 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-400">+23%</div>
                        <div class="text-xs text-gray-400 mt-1">{{ __('features/analytics.ministat1') }}</div>
                    </div>
                    <div class="bg-yellow-600/10 border border-yellow-600/20 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-400">€450K</div>
                        <div class="text-xs text-gray-400 mt-1">{{ __('features/analytics.ministat2') }}</div>
                    </div>
                    <div class="bg-yellow-600/10 border border-yellow-600/20 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-400">89%</div>
                        <div class="text-xs text-gray-400 mt-1">{{ __('features/analytics.ministat3') }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Visual placeholder -->
            <div class="bg-gradient-to-br from-yellow-600/20 to-orange-600/20 border border-white/10 rounded-2xl p-8 aspect-square flex items-center justify-center">
                <svg class="w-48 h-48 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>
