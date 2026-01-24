<!-- US PRICES Section -->
@php
    $user = auth()->user();
    $preferredCurrency = $user?->preferred_currency ?? 'EUR';
    $needsConversion = $preferredCurrency && $preferredCurrency !== 'USD';
    $currencySymbol = $needsConversion ? \App\Services\CurrencyService::getSymbol($preferredCurrency) : '$';
    
    // Helper function to format USD price with conversion
    $formatUsdPrice = function($usdAmount) use ($needsConversion, $preferredCurrency, $currencySymbol) {
        if (!$usdAmount) return null;
        
        if ($needsConversion) {
            $converted = \App\Services\CurrencyService::convert($usdAmount, 'USD', $preferredCurrency);
            return [
                'display' => number_format($converted, 2),
                'original' => number_format($usdAmount, 2),
                'symbol' => $currencySymbol
            ];
        }
        
        return [
            'display' => number_format($usdAmount, 2),
            'original' => null,
            'symbol' => '$'
        ];
    };
@endphp
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <span>🇺🇸</span>
            <span>US PRICES</span>
            @if($needsConversion)
            <span class="text-xs text-gray-400 font-normal">({{ $currencySymbol }} {{ $preferredCurrency }})</span>
            @endif
        </h3>
        <a href="https://www.tcgplayer.com/" target="_blank" rel="noopener" class="text-blue-400 hover:text-blue-300 text-sm flex items-center gap-1">
            <span>TCGPLAYER</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
        </a>
    </div>
    
    @php
        $holofoil = !empty($tcgdxTcgplayer['holofoil']) ? $tcgdxTcgplayer['holofoil'] : null;
    @endphp
    
    <!-- Price Table -->
    <div class="border border-white/10 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-black/30">
                <tr class="border-b border-white/10">
                    <th class="text-left py-3 px-4 text-gray-400 font-medium uppercase text-xs">{{ __('Market') }}</th>
                    <th class="text-right py-3 px-4 text-gray-400 font-medium uppercase text-xs">TCGCSV</th>
                    <th class="text-right py-3 px-4 text-blue-400 font-medium uppercase text-xs">RapidAPI</th>
                    <th class="text-right py-3 px-4 text-purple-400 font-medium uppercase text-xs">TCGdex</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <!-- Market Price Row -->
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.market_price') }}</td>
                    <td class="py-3 px-4 text-right">
                        @if($latestPrice && $latestPrice->market_price)
                        @php $price = $formatUsdPrice($latestPrice->market_price); @endphp
                        <span class="text-lg font-bold text-white">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                        @else
                        <span class="text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($tcgPlayerMarketRapid)
                        @php $price = $formatUsdPrice($tcgPlayerMarketRapid); @endphp
                        <span class="text-lg font-bold text-white">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                        @else
                        <span class="text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($holofoil && isset($holofoil['marketPrice']))
                        @php $price = $formatUsdPrice($holofoil['marketPrice']); @endphp
                        <span class="text-lg font-bold text-white">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                        <span class="text-xs text-purple-400 block">Holo</span>
                        @else
                        <span class="text-gray-600">-</span>
                        @endif
                    </td>
                </tr>
                
                <!-- Low Price Row -->
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.low_price') }}</td>
                    <td class="py-3 px-4 text-right">
                        @if($latestPrice && $latestPrice->low_price)
                        @php $price = $formatUsdPrice($latestPrice->low_price); @endphp
                        <span class="text-base font-semibold text-gray-200">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                        @else
                        <span class="text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-600">-</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($holofoil && isset($holofoil['lowPrice']))
                        @php $price = $formatUsdPrice($holofoil['lowPrice']); @endphp
                        <span class="text-base font-semibold text-gray-200">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                        <span class="text-xs text-purple-400 block">Holo</span>
                        @else
                        <span class="text-gray-600">-</span>
                        @endif
                    </td>
                </tr>
                
                <!-- Mid Price Row -->
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.mid_price') }}</td>
                    <td class="py-3 px-4 text-right">
                        @if($latestPrice && $latestPrice->mid_price)
                        @php $price = $formatUsdPrice($latestPrice->mid_price); @endphp
                        <span class="text-base font-semibold text-gray-200">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                        @else
                        <span class="text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        @if($tcgPlayerMidRapid)
                        @php $price = $formatUsdPrice($tcgPlayerMidRapid); @endphp
                        <span class="text-base font-semibold text-gray-200">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                        @else
                        <span class="text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-600">-</span>
                    </td>
                </tr>
                
                <!-- High Price Row (only TCGCSV) -->
                @if($latestPrice && $latestPrice->high_price)
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 text-gray-300">{{ __('tcg/cards/show.high_price') }}</td>
                    <td class="py-3 px-4 text-right">
                        @php $price = $formatUsdPrice($latestPrice->high_price); @endphp
                        <span class="text-base font-semibold text-gray-200">{{ $price['symbol'] }}{{ $price['display'] }}</span>
                        @if($price['original'])
                        <span class="block text-xs text-gray-400">(${{ $price['original'] }})</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-600">-</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <span class="text-gray-600">-</span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="mt-3 text-xs text-gray-500">
        @if($latestPrice)
        <p>{{ __('TCGCSV last updated') }}: {{ $latestPrice->snapshot_at->diffForHumans() }}</p>
        @endif
    </div>
</div>
