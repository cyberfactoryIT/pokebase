<!-- SECTION 6: Security & Privacy -->
<div class="py-20 bg-[#0a0a0a]">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <div class="inline-block px-4 py-2 bg-gray-600/20 rounded-full text-gray-400 text-sm font-semibold mb-4">
                {{ __('features/security.badge') }}
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-6">{{ __('features/security.title') }}</h2>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">{{ __('features/security.subtitle') }}</p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach(['feat1', 'feat2', 'feat3', 'feat4', 'feat5', 'feat6'] as $feat)
            <div class="bg-[#161615] border border-white/10 rounded-xl p-6 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h4 class="font-bold mb-2">{{ __('features/security.' . $feat . '_title') }}</h4>
                <p class="text-sm text-gray-400">{{ __('features/security.' . $feat . '_text') }}</p>
            </div>
            @endforeach
        </div>
        
        <p class="text-2xl font-bold text-gray-400 text-center mb-8">{{ __('features/security.tagline') }}</p>
        
        <!-- Trust badges -->
        <div class="flex flex-wrap justify-center gap-6">
            <div class="px-6 py-3 bg-white/5 border border-white/10 rounded-lg font-semibold">🇪🇺 GDPR Compliant</div>
            <div class="px-6 py-3 bg-white/5 border border-white/10 rounded-lg font-semibold">🔒 SSL Encrypted</div>
            <div class="px-6 py-3 bg-white/5 border border-white/10 rounded-lg font-semibold">🖥️ EU Servers</div>
        </div>
    </div>
</div>
