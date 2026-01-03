<!-- EU PRICES Section -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <span>🇪🇺</span>
            <span>EU PRICES</span>
        </h3>
        @if($cardmarketUrl)
        <a href="{{ $cardmarketUrl }}" target="_blank" rel="noopener" class="text-emerald-400 hover:text-emerald-300 text-sm flex items-center gap-1">
            <span>CARDMARKET</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
        </a>
        @endif
    </div>
    
    @if($latestQuote)
    <!-- Cardmarket Price Quotes Table (Primary Source) -->
    <div class="border border-white/10 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-black/30">
                <tr class="border-b border-white/10">
                    <th class="text-left py-3 px-4 text-gray-400 font-medium uppercase text-xs">{{ __('Price Type') }}</th>
                    <th class="text-right py-3 px-4 text-emerald-400 font-medium uppercase text-xs">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @if($latestQuote->trend)
                <tr class="hover:bg-white/5 bg-emerald-500/10">
                    <td class="py-4 px-4 text-gray-200 font-semibold">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span>{{ __('tcg/cards/show.trend') }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-right">
                        <span class="text-2xl font-bold text-emerald-400">€{{ number_format($latestQuote->trend, 2) }}</span>
                    </td>
                </tr>
                @endif
                
                @if($latestQuote->avg)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.average') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-lg font-bold text-white">€{{ number_format($latestQuote->avg, 2) }}</span>
                    </td>
                </tr>
                @endif
                
                @if($latestQuote->low)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.low_price') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($latestQuote->low, 2) }}</span>
                    </td>
                </tr>
                @endif
                
                @if($latestQuote->avg7)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.7d_average') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($latestQuote->avg7, 2) }}</span>
                    </td>
                </tr>
                @endif
                
                @if($latestQuote->avg30)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.30d_average') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($latestQuote->avg30, 2) }}</span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
        
        @if($latestQuote->avg_holo || $latestQuote->low_holo || $latestQuote->trend_holo)
        <div class="border-t border-white/10 bg-yellow-500/5 p-4">
            <h4 class="text-sm font-semibold text-yellow-400 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                {{ __('tcg/cards/show.holofoil') }} {{ __('tcg/cards/show.prices') }}
            </h4>
            <div class="grid grid-cols-3 gap-3">
                @if($latestQuote->trend_holo)
                <div class="bg-black/30 rounded-lg p-3 border border-yellow-400/20">
                    <div class="text-xs text-gray-400 uppercase mb-1">{{ __('tcg/cards/show.trend') }}</div>
                    <div class="text-xl font-bold text-yellow-400">€{{ number_format($latestQuote->trend_holo, 2) }}</div>
                </div>
                @endif
                @if($latestQuote->avg_holo)
                <div class="bg-black/30 rounded-lg p-3 border border-yellow-400/20">
                    <div class="text-xs text-gray-400 uppercase mb-1">{{ __('tcg/cards/show.average') }}</div>
                    <div class="text-lg font-semibold text-white">€{{ number_format($latestQuote->avg_holo, 2) }}</div>
                </div>
                @endif
                @if($latestQuote->low_holo)
                <div class="bg-black/30 rounded-lg p-3 border border-yellow-400/20">
                    <div class="text-xs text-gray-400 uppercase mb-1">{{ __('tcg/cards/show.low_price') }}</div>
                    <div class="text-base font-semibold text-gray-200">€{{ number_format($latestQuote->low_holo, 2) }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <div class="mt-3 text-xs text-gray-500">
        @if($latestQuote->as_of_date)
        <p>{{ __('Cardmarket last updated') }}: {{ $latestQuote->as_of_date->diffForHumans() }}</p>
        @endif
    </div>
    @elseif($cardmarketAvgRapid || !empty($tcgdxCardmarket))
    <!-- Alternative Sources: RapidAPI and TCGdex side by side -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($cardmarketAvgRapid || $cardmarketLowRapid || $cardmarketTrendRapid)
        <!-- RapidAPI Cardmarket -->
        <div class="border border-blue-400/30 bg-blue-500/5 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-blue-400 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                RapidAPI
            </h4>
            <div class="space-y-2">
                @if($cardmarketTrendRapid)
                <div class="flex justify-between items-center py-2 border-b border-white/10">
                    <span class="text-gray-300 text-sm">{{ __('tcg/cards/show.trend') }}</span>
                    <span class="text-lg font-bold text-white">€{{ number_format($cardmarketTrendRapid, 2) }}</span>
                </div>
                @endif
                @if($cardmarketAvgRapid)
                <div class="flex justify-between items-center py-2 border-b border-white/10">
                    <span class="text-gray-300 text-sm">{{ __('tcg/cards/show.average') }}</span>
                    <span class="text-base font-semibold text-gray-200">€{{ number_format($cardmarketAvgRapid, 2) }}</span>
                </div>
                @endif
                @if($cardmarketLowRapid)
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-300 text-sm">{{ __('tcg/cards/show.low_price') }}</span>
                    <span class="text-base font-semibold text-gray-200">€{{ number_format($cardmarketLowRapid, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        @if(!empty($tcgdxCardmarket))
        <!-- TCGdex Cardmarket -->
        <div class="border border-purple-400/30 bg-purple-500/5 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-purple-400 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                TCGdex
            </h4>
            <div class="space-y-2">
                @if(isset($tcgdxCardmarket['trend']))
                <div class="flex justify-between items-center py-2 border-b border-white/10">
                    <span class="text-gray-300 text-sm">{{ __('tcg/cards/show.trend') }}</span>
                    <span class="text-lg font-bold text-white">€{{ number_format($tcgdxCardmarket['trend'], 2) }}</span>
                </div>
                @endif
                @if(isset($tcgdxCardmarket['avg']))
                <div class="flex justify-between items-center py-2 border-b border-white/10">
                    <span class="text-gray-300 text-sm">{{ __('tcg/cards/show.average') }}</span>
                    <span class="text-base font-semibold text-gray-200">€{{ number_format($tcgdxCardmarket['avg'], 2) }}</span>
                </div>
                @endif
                @if(isset($tcgdxCardmarket['low']))
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-300 text-sm">{{ __('tcg/cards/show.low_price') }}</span>
                    <span class="text-base font-semibold text-gray-200">€{{ number_format($tcgdxCardmarket['low'], 2) }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="text-center py-8 text-gray-400 border border-white/10 rounded-lg">
        <svg class="mx-auto h-12 w-12 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p>{{ __('tcg/cards/show.no_eu_prices') }}</p>
    </div>
    @endif
</div>
