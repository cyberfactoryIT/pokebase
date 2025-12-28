@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Your Deck Valuation</h1>
            <p class="text-gray-400">{{ $valuation->name }}</p>
        </div>

        @if(session('success'))
        <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4 mb-6">
            <p class="text-green-200">{{ session('success') }}</p>
        </div>
        @endif

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
                <span class="text-green-400 text-sm">Add Cards</span>
                <span class="text-green-400 text-sm">Your Info</span>
                <span class="text-blue-400 font-semibold text-sm">Valuation</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Cards -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Cards</p>
                        <p class="text-white text-2xl font-bold">{{ number_format($stats['total_cards']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Unique Cards -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Unique Cards</p>
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
                        <p class="text-gray-400 text-sm">Est. Total Value</p>
                        <p class="text-white text-2xl font-bold">${{ number_format($stats['total_value'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card List -->
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-6 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Card Details</h2>
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
                            $price = $item->tcgcsvProduct->prices()->first();
                        @endphp
                        @if($price && $price->market_price)
                        <p class="text-green-400 font-semibold">
                            ${{ number_format($price->market_price * $item->quantity, 2) }}
                        </p>
                        <p class="text-gray-500 text-xs">
                            ${{ number_format($price->market_price, 2) }} each
                        </p>
                        @else
                        <p class="text-gray-500 text-sm">N/A</p>
                        @endif
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
                    <h3 class="text-white font-semibold mb-2">Save This Valuation</h3>
                    <p class="text-blue-200 text-sm mb-4">You're logged in! Save this deck valuation to your account to access it anytime.</p>
                    <form method="POST" action="{{ route('pokemon.deck-valuation.attach', $guestDeck->uuid) }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                            Save to My Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="{{ route('pokemon.deck-valuation.step1') }}" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition text-center">
                Value Another Deck
            </a>
            @guest
            <a href="{{ route('register') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition text-center">
                Create Account
            </a>
            @endguest
        </div>
    </div>
</div>
@endsection
