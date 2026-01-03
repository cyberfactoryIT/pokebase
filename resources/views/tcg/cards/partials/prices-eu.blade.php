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
    
    @if($marketPriceEur > 0)
    <!-- Price Table -->
    <div class="border border-white/10 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-black/30">
                <tr class="border-b border-white/10">
                    <th class="text-left py-3 px-4 text-gray-400 font-medium uppercase text-xs">{{ __('Source') }}</th>
                    <th class="text-left py-3 px-4 text-gray-400 font-medium uppercase text-xs">{{ __('Market') }}</th>
                    <th class="text-right py-3 px-4 text-gray-400 font-medium uppercase text-xs">{{ __('Price') }}</th>
                    <th class="text-right py-3 px-4 text-gray-400 font-medium uppercase text-xs">{{ __('Change') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.average') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-lg font-bold text-white">€{{ number_format($marketPriceEur, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-400">Stable</span>
                    </td>
                </tr>
                
                @if($lowPriceEur)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.low_price') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($lowPriceEur, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                
                @if($trendPriceEur)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.trend') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($trendPriceEur, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                
                @if($avg7d)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.7d_average') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($avg7d, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                
                @if($avg30d)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.30d_average') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($avg30d, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                
                @if($avgHolo)
                <tr class="hover:bg-white/5 bg-yellow-500/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.average') }} ({{ __('tcg/cards/show.holofoil') }})</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-lg font-bold text-white">€{{ number_format($avgHolo, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-400">Stable</span>
                    </td>
                </tr>
                @endif
                
                @if($lowHolo)
                <tr class="hover:bg-white/5 bg-yellow-500/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.low_price') }} ({{ __('tcg/cards/show.holofoil') }})</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($lowHolo, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                
                @if($trendHolo)
                <tr class="hover:bg-white/5 bg-yellow-500/5">
                    <td class="py-3 px-4 text-gray-300">Cardmarket</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.trend') }} ({{ __('tcg/cards/show.holofoil') }})</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($trendHolo, 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                
                @if(!empty($tcgdxCardmarket))
                @if(isset($tcgdxCardmarket['avg']))
                <tr class="hover:bg-white/5 bg-purple-500/5">
                    <td class="py-3 px-4 text-gray-300">TCGdex</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.average') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-lg font-bold text-white">€{{ number_format($tcgdxCardmarket['avg'], 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-400">Stable</span>
                    </td>
                </tr>
                @endif
                
                @if(isset($tcgdxCardmarket['low']))
                <tr class="hover:bg-white/5 bg-purple-500/5">
                    <td class="py-3 px-4 text-gray-300">TCGdex</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.low_price') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($tcgdxCardmarket['low'], 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                
                @if(isset($tcgdxCardmarket['trend']))
                <tr class="hover:bg-white/5 bg-purple-500/5">
                    <td class="py-3 px-4 text-gray-300">TCGdex</td>
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.trend') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-base font-semibold text-gray-200">€{{ number_format($tcgdxCardmarket['trend'], 2) }}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-500">-</span>
                    </td>
                </tr>
                @endif
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="mt-3 text-xs text-gray-500">
        @if($latestQuote && $latestQuote->as_of_date)
        <p>{{ __('Cardmarket last updated') }}: {{ $latestQuote->as_of_date->diffForHumans() }}</p>
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
