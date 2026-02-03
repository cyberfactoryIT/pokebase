{{--
    CMAPI Card Prices Partial
    
    Props:
    - $card: CmapiCard model instance
    - $size: 'large'|'small' (optional, default 'large')
--}}

@php
    $user = auth()->user();
    $canSeePrices = $user && ($user->isAdvanced() || $user->isPremium());
    $preferredCurrency = $canSeePrices && $user ? ($user->preferred_currency ?? 'EUR') : 'EUR';
    $needsConversion = $preferredCurrency && $preferredCurrency !== 'EUR';
    $size = $size ?? 'large';
    $isLarge = $size === 'large';
@endphp

@if($card->price_eur || $card->price_usd || (isset($cardmarketPrices) && $cardmarketPrices))
    <div class="space-y-3">
        @if($card->price_eur)
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="{{ $isLarge ? 'text-sm' : 'text-xs' }} font-medium text-gray-400">
                {{ __('catalog.cardmarket') }} 
                @if(isset($cardmarketPrices) && $cardmarketPrices)
                    <span class="text-xs text-gray-500">({{ __('common.trend') }})</span>
                @endif
            </span>
            @if($canSeePrices && $needsConversion)
                @php
                    $convertedPrice = \App\Services\CurrencyService::convert($card->price_eur, 'EUR', $preferredCurrency);
                    $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                @endphp
                <div class="text-right">
                    <div class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-green-400">
                        {{ $symbol }}{{ number_format($convertedPrice, 2) }}
                    </div>
                    <div class="text-xs text-gray-500">(€{{ number_format($card->price_eur, 2) }})</div>
                </div>
            @else
                <span class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-green-400">
                    €{{ number_format($card->price_eur, 2) }}
                </span>
            @endif
        </div>
        @endif
        
        {{-- CardMarket Detailed Prices (Lorcana only) --}}
        @if(isset($cardmarketPrices) && $cardmarketPrices && $isLarge)
            @if($cardmarketPrices->low)
            <div class="flex justify-between items-center py-2 border-b border-white/10">
                <span class="text-sm text-gray-400">{{ __('common.price_low') }}</span>
                @if($canSeePrices && $needsConversion)
                    @php
                        $convertedLow = \App\Services\CurrencyService::convert($cardmarketPrices->low, 'EUR', $preferredCurrency);
                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                    @endphp
                    <div class="text-right">
                        <span class="text-lg font-semibold text-blue-400">{{ $symbol }}{{ number_format($convertedLow, 2) }}</span>
                        <div class="text-xs text-gray-500">(€{{ number_format($cardmarketPrices->low, 2) }})</div>
                    </div>
                @else
                    <span class="text-lg font-semibold text-blue-400">€{{ number_format($cardmarketPrices->low, 2) }}</span>
                @endif
            </div>
            @endif
            
            @if($cardmarketPrices->avg)
            <div class="flex justify-between items-center py-2 border-b border-white/10">
                <span class="text-sm text-gray-400">{{ __('common.price_avg') }}</span>
                @if($canSeePrices && $needsConversion)
                    @php
                        $convertedAvg = \App\Services\CurrencyService::convert($cardmarketPrices->avg, 'EUR', $preferredCurrency);
                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                    @endphp
                    <div class="text-right">
                        <span class="text-lg font-semibold text-yellow-400">{{ $symbol }}{{ number_format($convertedAvg, 2) }}</span>
                        <div class="text-xs text-gray-500">(€{{ number_format($cardmarketPrices->avg, 2) }})</div>
                    </div>
                @else
                    <span class="text-lg font-semibold text-yellow-400">€{{ number_format($cardmarketPrices->avg, 2) }}</span>
                @endif
            </div>
            @endif
            
            @if($cardmarketPrices->avg7)
            <div class="flex justify-between items-center py-2 border-b border-white/10">
                <span class="text-sm text-gray-400">{{ __('common.price_avg7') }}</span>
                @if($canSeePrices && $needsConversion)
                    @php
                        $convertedAvg7 = \App\Services\CurrencyService::convert($cardmarketPrices->avg7, 'EUR', $preferredCurrency);
                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                    @endphp
                    <div class="text-right">
                        <span class="text-base text-gray-300">{{ $symbol }}{{ number_format($convertedAvg7, 2) }}</span>
                        <div class="text-xs text-gray-500">(€{{ number_format($cardmarketPrices->avg7, 2) }})</div>
                    </div>
                @else
                    <span class="text-base text-gray-300">€{{ number_format($cardmarketPrices->avg7, 2) }}</span>
                @endif
            </div>
            @endif
            
            @if($cardmarketPrices->avg30)
            <div class="flex justify-between items-center py-2 border-b border-white/10">
                <span class="text-sm text-gray-400">{{ __('common.price_avg30') }}</span>
                @if($canSeePrices && $needsConversion)
                    @php
                        $convertedAvg30 = \App\Services\CurrencyService::convert($cardmarketPrices->avg30, 'EUR', $preferredCurrency);
                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                    @endphp
                    <div class="text-right">
                        <span class="text-base text-gray-300">{{ $symbol }}{{ number_format($convertedAvg30, 2) }}</span>
                        <div class="text-xs text-gray-500">(€{{ number_format($cardmarketPrices->avg30, 2) }})</div>
                    </div>
                @else
                    <span class="text-base text-gray-300">€{{ number_format($cardmarketPrices->avg30, 2) }}</span>
                @endif
            </div>
            @endif
            
            {{-- Foil/Holo prices if available --}}
            @if($cardmarketPrices->trend_holo || $cardmarketPrices->avg_holo || $cardmarketPrices->low_holo)
                <div class="mt-4 pt-4 border-t border-white/20">
                    <div class="text-xs font-semibold text-gray-400 mb-2 uppercase">{{ __('common.foil_prices') }}</div>
                    
                    @if($cardmarketPrices->trend_holo)
                    <div class="flex justify-between items-center py-2 border-b border-white/10">
                        <span class="text-sm text-gray-400">{{ __('common.trend') }}</span>
                        @if($canSeePrices && $needsConversion)
                            @php
                                $convertedTrendHolo = \App\Services\CurrencyService::convert($cardmarketPrices->trend_holo, 'EUR', $preferredCurrency);
                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                            @endphp
                            <div class="text-right">
                                <span class="text-lg font-semibold text-purple-400">{{ $symbol }}{{ number_format($convertedTrendHolo, 2) }}</span>
                                <div class="text-xs text-gray-500">(€{{ number_format($cardmarketPrices->trend_holo, 2) }})</div>
                            </div>
                        @else
                            <span class="text-lg font-semibold text-purple-400">€{{ number_format($cardmarketPrices->trend_holo, 2) }}</span>
                        @endif
                    </div>
                    @endif
                    
                    @if($cardmarketPrices->avg_holo)
                    <div class="flex justify-between items-center py-2 border-b border-white/10">
                        <span class="text-sm text-gray-400">{{ __('common.price_avg') }}</span>
                        @if($canSeePrices && $needsConversion)
                            @php
                                $convertedAvgHolo = \App\Services\CurrencyService::convert($cardmarketPrices->avg_holo, 'EUR', $preferredCurrency);
                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                            @endphp
                            <div class="text-right">
                                <span class="text-base text-gray-300">{{ $symbol }}{{ number_format($convertedAvgHolo, 2) }}</span>
                                <div class="text-xs text-gray-500">(€{{ number_format($cardmarketPrices->avg_holo, 2) }})</div>
                            </div>
                        @else
                            <span class="text-base text-gray-300">€{{ number_format($cardmarketPrices->avg_holo, 2) }}</span>
                        @endif
                    </div>
                    @endif
                    
                    @if($cardmarketPrices->low_holo)
                    <div class="flex justify-between items-center py-2 border-b border-white/10">
                        <span class="text-sm text-gray-400">{{ __('common.price_low') }}</span>
                        @if($canSeePrices && $needsConversion)
                            @php
                                $convertedLowHolo = \App\Services\CurrencyService::convert($cardmarketPrices->low_holo, 'EUR', $preferredCurrency);
                                $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                            @endphp
                            <div class="text-right">
                                <span class="text-base text-gray-300">{{ $symbol }}{{ number_format($convertedLowHolo, 2) }}</span>
                                <div class="text-xs text-gray-500">(€{{ number_format($cardmarketPrices->low_holo, 2) }})</div>
                            </div>
                        @else
                            <span class="text-base text-gray-300">€{{ number_format($cardmarketPrices->low_holo, 2) }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            @endif
            
            <div class="text-xs text-gray-500 mt-3 p-2 bg-gray-800/30 rounded">
                📅 {{ __('common.last_updated') }}: {{ \Carbon\Carbon::parse($cardmarketPrices->as_of_date)->format('d/m/Y') }}
            </div>
        @endif
        
        @if($card->price_usd)
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="{{ $isLarge ? 'text-sm' : 'text-xs' }} font-medium text-gray-400">{{ __('catalog.tcgplayer') }}</span>
            @if($canSeePrices && $needsConversion && $preferredCurrency !== 'USD')
                @php
                    $convertedPrice = \App\Services\CurrencyService::convert($card->price_usd, 'USD', $preferredCurrency);
                    $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                @endphp
                <div class="text-right">
                    <div class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-blue-400">
                        {{ $symbol }}{{ number_format($convertedPrice, 2) }}
                    </div>
                    <div class="text-xs text-gray-500">($ {{ number_format($card->price_usd, 2) }})</div>
                </div>
            @else
                <span class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-blue-400">
                    ${{ number_format($card->price_usd, 2) }}
                </span>
            @endif
        </div>
        @endif
        
        <div class="text-xs text-gray-500 mt-4">
            {{ __('catalog.price_from_cardmarket') }}
            @if($canSeePrices && $needsConversion)
                <br>{{ __('catalog.converted_to') }} {{ $preferredCurrency }} {{ __('catalog.original_price_below') }}
            @endif
        </div>
    </div>
@else
    <div class="text-sm text-gray-400 text-center py-4">
        {{ __('catalog.no_pricing_data') }}
    </div>
@endif
