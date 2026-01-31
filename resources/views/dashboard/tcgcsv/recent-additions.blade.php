<!-- Recent Additions Section -->
@if($recentAdditions && $recentAdditions->isNotEmpty())
<div class="mb-8">
    <h3 class="font-semibold text-2xl text-white mb-6 flex items-center gap-3">
        <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ __('dashboard.recent_additions') }}
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($recentAdditions as $item)
        <a href="{{ route('tcg.cards.show', $item->card->product_id) }}" 
           class="group bg-white/5 border border-white/10 rounded-xl overflow-hidden hover:border-blue-500/50 hover:shadow-lg hover:shadow-blue-500/20 transition-all">
            <!-- Card Image -->
            <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 aspect-[5/7] overflow-hidden">
                @if($item->card->image_url)
                    <img src="{{ $item->card->image_url }}" 
                         alt="{{ $item->card->name }}" 
                         class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                @else
                    <div class="flex items-center justify-center h-full text-gray-500">
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
                
                <!-- Quantity Badge -->
                @if($item->quantity > 1)
                <div class="absolute top-2 right-2 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-bold shadow-lg">
                    x{{ $item->quantity }}
                </div>
                @endif
                
                <!-- Condition Badge -->
                @if($item->condition)
                <div class="absolute bottom-2 left-2 bg-black/80 text-white px-2 py-1 rounded text-xs font-semibold">
                    {{ $item->condition }}
                </div>
                @endif
            </div>
            
            <!-- Card Info -->
            <div class="p-4">
                <h4 class="font-semibold text-white mb-1 line-clamp-1 group-hover:text-blue-400 transition">
                    {{ $item->card->name }}
                </h4>
                <p class="text-sm text-gray-400 mb-2">
                    {{ $item->card->group->name ?? __('dashboard.unknown_set') }}
                </p>
                
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500">
                        {{ $item->created_at->diffForHumans() }}
                    </span>
                    
                    @if($isTcgdex)
                        @if($item->cached_price && $item->cached_price > 0)
                        <span class="text-green-400 font-semibold">
                            €{{ number_format($item->cached_price, 2) }}
                        </span>
                        @endif
                    @else
                        @php
                            $latestPrice = $card->prices->first();
                        @endphp
                        @if($latestPrice && $latestPrice->market_price)
                        <span class="text-green-400 font-semibold">
                            {{ $latestPrice->currency }} {{ number_format($latestPrice->market_price, 2) }}
                        </span>
                        @endif
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    
    <!-- View All Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('collection.index') }}" 
           class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 font-semibold transition group">
            <span>{{ __('dashboard.view_all_collection') }}</span>
            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </a>
    </div>
</div>
@endif
