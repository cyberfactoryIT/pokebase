<!-- Featured Expansions - TCGDEX -->
@if($featuredExpansions->isNotEmpty())
<div class="mb-8">
    <div class="flex items-center justify-end mb-3">
        <a href="{{ route('pokemon.sets') }}" class="text-sm text-gray-400 hover:text-white transition flex items-center gap-1 group">
            <span>{{ __('dashboard.view_all') }}</span>
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </a>
    </div>
    
    <div class="relative group">
        <!-- Carousel Container -->
        <div id="expansion-carousel" class="flex gap-4 overflow-x-auto pb-4 scroll-smooth scrollbar-hide">
            @foreach($featuredExpansions as $expansion)
                <a href="{{ route('pokemon.set', $expansion->tcgdex_id) }}" 
                   class="flex-shrink-0 w-48 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg overflow-hidden transition group/card">
                    
                    <!-- Expansion Logo -->
                    <div class="aspect-video bg-gradient-to-br from-white/5 to-white/0 flex items-center justify-center p-4">
                        @if($expansion->logo_url)
                            <img src="{{ $expansion->logo_url }}" 
                                 alt="{{ is_array($expansion->name) ? ($expansion->name['en'] ?? $expansion->tcgdex_id) : $expansion->name }}" 
                                 class="w-full h-full object-contain group-hover/card:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-center">
                                <svg class="w-16 h-16 text-white/30 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Expansion Info -->
                    <div class="p-3 border-t border-white/10">
                        <h4 class="text-white font-medium text-sm mb-1 truncate">
                            {{ is_array($expansion->name) ? ($expansion->name['en'] ?? $expansion->tcgdex_id) : $expansion->name }}
                        </h4>
                        @if($expansion->released_at)
                            <p class="text-gray-400 text-xs">{{ $expansion->released_at->format('Y') }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        
        <!-- Navigation Arrows -->
        @if($featuredExpansions->count() > 4)
        <button id="carousel-prev" 
                class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        
        <button id="carousel-next" 
                class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        @endif
    </div>
</div>

@if($featuredExpansions->count() > 4)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('expansion-carousel');
    const prevBtn = document.getElementById('carousel-prev');
    const nextBtn = document.getElementById('carousel-next');
    
    if (carousel && prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: -200, behavior: 'smooth' });
        });
        
        nextBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: 200, behavior: 'smooth' });
        });
    }
});
</script>
@endif
@endif
