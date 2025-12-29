@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $preferredCurrency = $user?->preferred_currency;
    $defaultCurrency = $preferredCurrency ?: 'EUR';
    
    // If user has a preferred currency, convert the prices
    if ($preferredCurrency) {
        $displayValueEur = \App\Services\CurrencyService::convert($stats['total_value_eur'], 'EUR', $preferredCurrency);
        $displayValueUsd = \App\Services\CurrencyService::convert($stats['total_value_usd'], 'USD', $preferredCurrency);
        $currencySymbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
    } else {
        $displayValueEur = $stats['total_value_eur'];
        $displayValueUsd = $stats['total_value_usd'];
        $currencySymbol = null;
    }
@endphp
<div class="bg-black min-h-screen py-8" x-data="{ 
    currency: localStorage.getItem('valuationCurrency') || '{{ $defaultCurrency }}',
    preferredCurrency: '{{ $preferredCurrency }}',
    setCurrency(curr) {
        this.currency = curr;
        localStorage.setItem('valuationCurrency', curr);
    }
}">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">{{ __('deckvaluation.step3_title') }}</h1>
            <p class="text-gray-400">{{ $valuation->name }}</p>
        </div>

        @if(session('success'))
        <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4 mb-6">
            <p class="text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        <!-- Currency Toggle -->
        <div class="mb-6 flex justify-end">
            <div class="inline-flex bg-[#161615] border border-white/15 rounded-lg p-1">
                <button 
                    @click="setCurrency('EUR')"
                    :class="currency === 'EUR' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                    class="px-4 py-2 rounded-md font-medium transition"
                >
                    EUR (Cardmarket)
                </button>
                <button 
                    @click="setCurrency('USD')"
                    :class="currency === 'USD' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                    class="px-4 py-2 rounded-md font-medium transition"
                >
                    USD (TCGPlayer)
                </button>
            </div>
        </div>

        <!-- Progress indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="bg-green-500 text-white rounded-full w-10 h-10 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="w-16 h-1 bg-green-500"></div>
                    <div class="bg-green-500 text-white rounded-full w-10 h-10 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="w-16 h-1 bg-green-500"></div>
                    <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">3</div>
                </div>
            </div>
            <div class="flex justify-between max-w-md mx-auto mt-2">
                <span class="text-green-400 text-sm">{{ __('deckvaluation.progress_step1') }}</span>
                <span class="text-green-400 text-sm">{{ __('deckvaluation.progress_step2') }}</span>
                <span class="text-blue-400 font-semibold text-sm">{{ __('deckvaluation.progress_step3') }}</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Unique Cards -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">{{ __('deckvaluation.step3_unique_cards') }}</p>
                        <p class="text-white text-2xl font-bold">{{ number_format($stats['unique_cards']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Value -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">{{ __('deckvaluation.step3_total_value') }}</p>
                        
                        @if($preferredCurrency)
                            <!-- User has preferred currency - show converted price with original -->
                            <div x-show="currency === 'EUR'">
                                <p class="text-white text-2xl font-bold">
                                    @php
                                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                        $formatted = number_format($displayValueEur, 2);
                                        if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                            echo "{$symbol}{$formatted}";
                                        } else {
                                            echo "{$formatted} {$symbol}";
                                        }
                                    @endphp
                                </p>
                                <p class="text-gray-500 text-xs">{{ __('collection/index.original_price') }}: €{{ number_format($stats['total_value_eur'], 2) }}</p>
                            </div>
                            <div x-show="currency === 'USD'">
                                <p class="text-white text-2xl font-bold">
                                    @php
                                        $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                        $formatted = number_format($displayValueUsd, 2);
                                        if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                            echo "{$symbol}{$formatted}";
                                        } else {
                                            echo "{$formatted} {$symbol}";
                                        }
                                    @endphp
                                </p>
                                <p class="text-gray-500 text-xs">{{ __('collection/index.original_price') }}: ${{ number_format($stats['total_value_usd'], 2) }}</p>
                            </div>
                        @else
                            <!-- No preferred currency - show default EUR/USD -->
                            <p class="text-white text-2xl font-bold" x-show="currency === 'EUR'">
                                €{{ number_format($stats['total_value_eur'], 2) }}
                            </p>
                            <p class="text-white text-2xl font-bold" x-show="currency === 'USD'">
                                ${{ number_format($stats['total_value_usd'], 2) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card List -->
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-6 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">{{ __('deckvaluation.step3_card_details') }}</h2>
            <div class="space-y-3">
                @foreach($valuation->items as $item)
                <div class="bg-black/30 border border-white/10 rounded-lg p-4 flex items-center gap-4">
                    @if($item->tcgcsvProduct->image_url)
                    <img src="{{ $item->tcgcsvProduct->image_url }}" alt="{{ $item->tcgcsvProduct->name }}" class="w-16 h-22 object-cover rounded">
                    @endif
                    <div class="flex-1">
                        <h3 class="text-white font-semibold">{{ $item->tcgcsvProduct->name }}</h3>
                        <p class="text-gray-400 text-sm">
                            {{ $item->tcgcsvProduct->group?->name ?? 'Unknown Set' }} • 
                            {{ $item->tcgcsvProduct->card_number }}
                        </p>
                        <p class="text-gray-500 text-xs">Qty: {{ $item->quantity }}</p>
                    </div>
                    <div class="text-right">
                        @php
                            $tcgPrice = $item->tcgcsvProduct->prices()->first();
                            $marketPriceUsd = $tcgPrice?->market_price ?? 0;
                            
                            // EUR price from RapidAPI Cardmarket data
                            $marketPriceEur = 0;
                            $rapidapiCard = $item->tcgcsvProduct->rapidapiCard;
                            if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                                $marketPriceEur = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                            }
                            
                            // Convert to preferred currency if set
                            if ($preferredCurrency) {
                                $convertedPriceEur = \App\Services\CurrencyService::convert($marketPriceEur, 'EUR', $preferredCurrency);
                                $convertedPriceUsd = \App\Services\CurrencyService::convert($marketPriceUsd, 'USD', $preferredCurrency);
                            }
                        @endphp
                        
                        <!-- EUR Price -->
                        <div x-show="currency === 'EUR'">
                            @if($marketPriceEur > 0)
                                @if($preferredCurrency)
                                    <p class="text-green-400 font-semibold">
                                        @php
                                            $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                            $totalConverted = $convertedPriceEur * $item->quantity;
                                            $formatted = number_format($totalConverted, 2);
                                            if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                echo "{$symbol}{$formatted}";
                                            } else {
                                                echo "{$formatted} {$symbol}";
                                            }
                                        @endphp
                                    </p>
                                    <p class="text-gray-500 text-xs">
                                        (€{{ number_format($marketPriceEur * $item->quantity, 2) }})
                                    </p>
                                    <p class="text-gray-500 text-xs">
                                        @php
                                            $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                            $eachConverted = number_format($convertedPriceEur, 2);
                                            if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                echo "{$symbol}{$eachConverted} each";
                                            } else {
                                                echo "{$eachConverted} {$symbol} each";
                                            }
                                        @endphp
                                    </p>
                                @else
                                    <p class="text-green-400 font-semibold">
                                        €{{ number_format($marketPriceEur * $item->quantity, 2) }}
                                    </p>
                                    <p class="text-gray-500 text-xs">
                                        €{{ number_format($marketPriceEur, 2) }} each
                                    </p>
                                @endif
                            @else
                                <p class="text-gray-500 text-sm">N/A</p>
                            @endif
                        </div>
                        
                        <!-- USD Price -->
                        <div x-show="currency === 'USD'">
                            @if($marketPriceUsd > 0)
                                @if($preferredCurrency)
                                    <p class="text-green-400 font-semibold">
                                        @php
                                            $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                            $totalConverted = $convertedPriceUsd * $item->quantity;
                                            $formatted = number_format($totalConverted, 2);
                                            if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                echo "{$symbol}{$formatted}";
                                            } else {
                                                echo "{$formatted} {$symbol}";
                                            }
                                        @endphp
                                    </p>
                                    <p class="text-gray-500 text-xs">
                                        (${{ number_format($marketPriceUsd * $item->quantity, 2) }})
                                    </p>
                                    <p class="text-gray-500 text-xs">
                                        @php
                                            $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                                            $eachConverted = number_format($convertedPriceUsd, 2);
                                            if (in_array($preferredCurrency, ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                                echo "{$symbol}{$eachConverted} each";
                                            } else {
                                                echo "{$eachConverted} {$symbol} each";
                                            }
                                        @endphp
                                    </p>
                                @else
                                    <p class="text-green-400 font-semibold">
                                        ${{ number_format($marketPriceUsd * $item->quantity, 2) }}
                                    </p>
                                    <p class="text-gray-500 text-xs">
                                        ${{ number_format($marketPriceUsd, 2) }} each
                                    </p>
                                @endif
                            @else
                                <p class="text-gray-500 text-sm">N/A</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Attach to Account (if logged in and not attached) -->
        @if($canAttach)
        <div class="bg-blue-900/20 border border-blue-500/30 rounded-xl p-6 mb-8">
            <div class="flex items-start gap-4">
                <div class="bg-blue-500/20 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-semibold mb-2">{{ __('deckvaluation.step3_save_title') }}</h3>
                    <p class="text-blue-200 text-sm mb-4">{{ __('deckvaluation.step3_save_description') }}</p>
                    <form method="POST" action="{{ route('pokemon.deck-valuation.attach', $guestDeck->uuid) }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            {{ __('deckvaluation.step3_save_button') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="{{ route('pokemon.deck-valuation.step1') }}" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition text-center">
                {{ __('deckvaluation.step3_value_another') }}
            </a>
            @guest
            <a href="{{ route('register') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition text-center">
                {{ __('deckvaluation.step3_create_account') }}
            </a>
            @endguest
        </div>
    </div>
</div>
@endsection
