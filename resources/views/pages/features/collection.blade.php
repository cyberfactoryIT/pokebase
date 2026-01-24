<!-- SECTION 1: Smart Collection Manager -->
<div class="py-20 bg-black">
    <div class="container mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Content -->
            <div>
                <div class="inline-block px-4 py-2 bg-blue-600/20 rounded-full text-blue-400 text-sm font-semibold mb-4">
                    {{ __('features/collection.badge') }}
                </div>
                <h2 class="text-4xl font-bold mb-6">{{ __('features/collection.title') }}</h2>
                <p class="text-xl text-gray-400 mb-8">{{ __('features/collection.subtitle') }}</p>
                
                <div class="space-y-4">
                    @foreach([ 'feat2', 'feat3', 'feat4', 'feat5'] as $feat)
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-400 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold mb-1">{{ __('features/collection.' . $feat . '_title') }}</h4>
                            <p class="text-gray-400">{{ __('features/collection.' . $feat . '_text') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <p class="text-2xl font-bold text-blue-400 mt-8">{{ __('features/collection.tagline') }}</p>
            </div>
            
            <!-- Visual placeholder -->
            <div class="bg-gradient-to-br from-blue-600/20 to-purple-600/20 border border-white/10 rounded-2xl p-8 aspect-square flex items-center justify-center">
                <svg class="w-48 h-48 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
        </div>
    </div>
</div>
