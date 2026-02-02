<!-- Missing Cards Section - CMAPI (Lorcana/One Piece) -->
@php
    // Get user's most collected set
    $userSetCounts = \App\Models\UserCollection::where('user_id', Auth::id())
        ->whereNotNull('cmapi_card_id')
        ->join('cmapi_cards', 'user_collection.cmapi_card_id', '=', 'cmapi_cards.id')
        ->select('cmapi_cards.set_cmapi_id', \DB::raw('COUNT(*) as card_count'))
        ->groupBy('cmapi_cards.set_cmapi_id')
        ->orderByDesc('card_count')
        ->first();
    
    $topSet = null;
    $missingCards = collect();
    $completionPercentage = 0;
    $totalValueEur = 0;
    
    if ($userSetCounts) {
        $topSet = \App\Models\Cmapi\CmapiSet::find($userSetCounts->set_cmapi_id);
        
        if ($topSet) {
            // Get owned card IDs for this set
            $ownedCardIds = \App\Models\UserCollection::where('user_id', Auth::id())
                ->join('cmapi_cards', 'user_collection.cmapi_card_id', '=', 'cmapi_cards.id')
                ->where('cmapi_cards.set_cmapi_id', $topSet->id)
                ->pluck('cmapi_cards.id')
                ->toArray();
            
            // Get missing cards
            $missingCards = \App\Models\Cmapi\CmapiCard::where('set_cmapi_id', $topSet->id)
                ->whereNotIn('id', $ownedCardIds)
                ->orderByRaw('CAST(number AS UNSIGNED), number')
                ->get();
            
            // Calculate stats
            $totalCards = \App\Models\Cmapi\CmapiCard::where('set_cmapi_id', $topSet->id)->count();
            $ownedCards = count($ownedCardIds);
            $completionPercentage = $totalCards > 0 ? round(($ownedCards / $totalCards) * 100) : 0;
            $totalValueEur = $missingCards->sum('price_eur');
        }
    }
@endphp

@if($topSet && $missingCards->isNotEmpty())
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-semibold text-xl text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                {{ __('dashboard.missing_cards_from') }}: {{ $topSet->name }}
            </h3>
            <p class="text-sm text-gray-400 mt-1">
                {{ __('dashboard.completion') }}: {{ $completionPercentage }}%
                @if($totalValueEur > 0)
                    <span class="mx-2">•</span>
                    <span class="text-purple-400">{{ __('dashboard.value_missing') }}: €{{ number_format($totalValueEur, 2) }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('cmapi.sets.show', [$currentGame->slug, $topSet->cmapi_episode]) }}" 
           class="text-sm text-purple-400 hover:text-purple-300 transition flex items-center gap-1">
            <span>{{ __('dashboard.view_set') }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </a>
    </div>
    
    <!-- Progress Bar -->
    <div class="mb-4">
        <div class="w-full bg-white/10 rounded-full h-2">
            <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-2 rounded-full transition-all duration-500" 
                 style="width: {{ $completionPercentage }}%"></div>
        </div>
    </div>
    
    <!-- Missing Cards Horizontal Scroll -->
    <div class="relative">
        <div class="flex gap-3 overflow-x-auto pb-4 scrollbar-hide">
            @foreach($missingCards->take(12) as $card)
                <a href="{{ route('cmapi.cards.show', [$currentGame->slug, $card->cmapi_id]) }}" 
                   class="flex-shrink-0 w-32 group">
                    <div class="bg-white/5 border border-white/10 hover:border-purple-500/50 rounded-lg overflow-hidden transition-all">
                        <!-- Card Image -->
                        <div class="aspect-[2/3] bg-gradient-to-br from-gray-800 to-gray-900 relative">
                            @if($card->image_large_url ?? $card->image_small_url)
                                <img src="{{ $card->image_large_url ?? $card->image_small_url }}" 
                                     alt="{{ $card->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                     loading="lazy">
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Missing Badge -->
                            <div class="absolute top-1 right-1 bg-purple-500 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                                {{ __('dashboard.missing') }}
                            </div>
                        </div>
                        
                        <!-- Card Info -->
                        <div class="p-2 border-t border-white/10">
                            <p class="text-white text-xs font-medium truncate">{{ $card->name }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-400">#{{ $card->number }}</span>
                                @if($card->price_eur)
                                    <span class="text-xs text-purple-400 font-semibold">€{{ number_format($card->price_eur, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
            
            @if($missingCards->count() > 12)
                <div class="flex-shrink-0 w-32 flex items-center justify-center">
                    <a href="{{ route('cmapi.sets.show', [$currentGame->slug, $topSet->cmapi_episode]) }}" 
                       class="flex flex-col items-center justify-center gap-2 text-center p-4 bg-white/5 border-2 border-dashed border-white/20 rounded-lg hover:border-purple-500/50 transition group">
                        <svg class="w-8 h-8 text-purple-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                        <span class="text-xs text-purple-400 font-medium">+{{ $missingCards->count() - 12 }} {{ __('dashboard.more') }}</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
