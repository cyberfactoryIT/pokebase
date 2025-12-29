@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#161615] py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#1a1919] overflow-hidden shadow-xl sm:rounded-lg p-6 border border-white/10">
                <h1 class="text-3xl font-bold text-white mb-6">
                    {{ __('deck_evaluation.packages.title') }}
                </h1>

                @if($activePurchase)
                    <!-- Active Purchase Status -->
                    <div class="mb-8 p-6 bg-emerald-900/30 border border-emerald-500/50 rounded-lg">
                        <h2 class="text-xl font-semibold text-emerald-400 mb-4">
                            {{ __('deck_evaluation.packages.active_purchase') }}
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-white">
                            <div>
                                <div class="text-sm text-gray-400">{{ __('deck_evaluation.packages.package') }}</div>
                                <div class="text-lg font-semibold">{{ $activePurchase->package->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-400">{{ __('deck_evaluation.packages.cards_remaining') }}</div>
                                <div class="text-lg font-semibold">
                                    @if($activePurchase->cards_limit === null)
                                        {{ __('deck_evaluation.packages.unlimited') }}
                                    @else
                                        {{ $activePurchase->remaining_cards }} / {{ $activePurchase->cards_limit }}
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-400">{{ __('deck_evaluation.packages.expires') }}</div>
                                <div class="text-lg font-semibold">{{ $activePurchase->expires_at->format('Y-m-d') }}</div>
                            </div>
                        </div>
                    </div>
                @elseif($summary['type'] === 'free')
                    <!-- Free Tier Status -->
                    <div class="mb-8 p-6 bg-blue-900/30 border border-blue-500/50 rounded-lg">
                        <h2 class="text-xl font-semibold text-blue-400 mb-4">
                            {{ __('deck_evaluation.packages.free_tier') }}
                        </h2>
                        <div class="text-white">
                            <div class="text-sm text-gray-400">{{ __('deck_evaluation.packages.cards_remaining') }}</div>
                            <div class="text-lg font-semibold">
                                {{ $summary['cards_remaining'] }} / {{ $summary['cards_limit'] }} {{ __('deck_evaluation.packages.free_cards') }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Available Packages -->
                <h2 class="text-2xl font-semibold text-white mb-6">
                    {{ __('deck_evaluation.packages.available_packages') }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($packages as $package)
                        <div class="bg-gray-700 rounded-lg p-6 border border-gray-600 hover:border-emerald-500 transition">
                            <h3 class="text-xl font-bold text-white mb-2">{{ $package->name }}</h3>
                            <div class="text-3xl font-bold text-emerald-400 mb-4">{{ $package->formatted_price }}</div>
                            
                            <ul class="space-y-2 mb-6 text-gray-300">
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    @if($package->max_cards)
                                        {{ __('deck_evaluation.packages.max_cards', ['count' => $package->max_cards]) }}
                                    @else
                                        {{ __('deck_evaluation.packages.unlimited_cards') }}
                                    @endif
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ __('deck_evaluation.packages.validity_days', ['days' => $package->validity_days]) }}
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    @if($package->allows_multiple_decks)
                                        {{ __('deck_evaluation.packages.multiple_decks') }}
                                    @else
                                        {{ __('deck_evaluation.packages.single_deck') }}
                                    @endif
                                </li>
                            </ul>

                            <a href="{{ route('deck-evaluation.packages.show', $package) }}" 
                               class="block w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-center rounded-lg transition font-semibold">
                                {{ __('deck_evaluation.packages.select') }}
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('pokemon.deck-valuation.step1') }}" 
                       class="text-emerald-400 hover:text-emerald-300 underline">
                        {{ __('deck_evaluation.packages.back_to_evaluation') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
