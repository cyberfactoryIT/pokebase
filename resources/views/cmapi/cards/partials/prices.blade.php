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

@if($card->price_eur || $card->price_usd)
    <div class="space-y-3">
        @if($card->price_eur)
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="{{ $isLarge ? 'text-sm' : 'text-xs' }} font-medium text-gray-400">CardMarket</span>
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
        
        @if($card->price_usd)
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="{{ $isLarge ? 'text-sm' : 'text-xs' }} font-medium text-gray-400">TCGPlayer</span>
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
            Prices from CardMarket API
            @if($canSeePrices && $needsConversion)
                <br>Converted to {{ $preferredCurrency }} (original price shown below)
            @endif
        </div>
    </div>
@else
    <div class="text-sm text-gray-400 text-center py-4">
        No price data available
    </div>
@endif
