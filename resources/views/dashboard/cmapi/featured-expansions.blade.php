<!-- Featured Expansions - CMAPI (Lorcana/One Piece) -->
@if($featuredExpansions->isNotEmpty())
<div class="mb-8">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold text-white">{{ __('dashboard.featured_expansions') }}</h3>
        <a href="/{{ $currentGame->slug }}/sets" class="text-sm text-gray-400 hover:text-white transition flex items-center gap-1 group">
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
                <a href="/{{ $currentGame->slug }}/sets/{{ $expansion->cmapi_episode }}" 
                   class="flex-shrink-0 w-48 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg overflow-hidden transition group/card">
                    
                    <!-- Expansion Logo -->
                    <div class="aspect-video bg-gradient-to-br from-white/5 to-white/0 flex items-center justify-center p-4">
                        @if($expansion->logo_url)
                            <img src="{{ $expansion->logo_url }}" 
                                 alt="{{ $expansion->name }}" 
                                 class="w-full h-full object-contain group-hover/card:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white/40 mb-2">{{ $expansion->cmapi_episode }}</div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Expansion Info -->
                    <div class="p-3 border-t border-white/10">
                        <h4 class="text-white font-medium text-sm mb-1 truncate">
                            {{ $expansion->name }}
                        </h4>
                        <div class="flex items-center justify-between text-xs">
                            @if($expansion->release_date)
                                <span class="text-gray-400">{{ $expansion->release_date->format('Y') }}</span>
                            @endif
                            @if($expansion->card_count)
                                <span class="text-gray-500">{{ $expansion->card_count }}{{ __('catalog.cards_suffix') }}</span>
                            @endif
                        </div>
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
    let autoScrollInterval;
    let isUserInteracting = false;
    
    if (carousel && prevBtn && nextBtn) {
        // Auto scroll function with wrap-around
        function autoScroll() {
            if (!isUserInteracting) {
                const step = 1;
                const maxScroll = carousel.scrollWidth - carousel.clientWidth;
                const nextPos = carousel.scrollLeft + step;

                if (nextPos >= maxScroll) {
                    // Wrap back to the start when we hit the end
                    carousel.scrollLeft = 0;
                } else {
                    carousel.scrollLeft = nextPos;
                }
            }
        }
        
        // Start auto-scrolling
        autoScrollInterval = setInterval(autoScroll, 30);
        
        // Pause on hover
        carousel.addEventListener('mouseenter', () => {
            isUserInteracting = true;
        });
        
        carousel.addEventListener('mouseleave', () => {
            isUserInteracting = false;
        });
        
        // Manual navigation with wrap-around
        prevBtn.addEventListener('click', () => {
            const maxScroll = carousel.scrollWidth - carousel.clientWidth;

            if (carousel.scrollLeft <= 0) {
                // From the start, jump to the end
                carousel.scrollTo({ left: maxScroll, behavior: 'smooth' });
            } else {
                carousel.scrollBy({ left: -200, behavior: 'smooth' });
            }
        });
        
        nextBtn.addEventListener('click', () => {
            const maxScroll = carousel.scrollWidth - carousel.clientWidth;
            const nextPos = carousel.scrollLeft + 200;

            if (nextPos >= maxScroll) {
                // From (or near) the end, jump back to the start
                carousel.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                carousel.scrollBy({ left: 200, behavior: 'smooth' });
            }
        });
    }
});
</script>
@endif
@endif
