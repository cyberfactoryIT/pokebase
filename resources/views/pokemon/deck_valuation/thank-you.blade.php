@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-6">
        <!-- Success Icon -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-500/20 rounded-full mb-6">
                <svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-white mb-3">{{ __('deckvaluation.thanks_title') }}</h1>
            <p class="text-xl text-gray-300">{{ __('deckvaluation.thanks_subtitle') }}</p>
        </div>

        <!-- Main Card -->
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-8 mb-6">
            <div class="text-center mb-6">
                <div class="inline-block bg-blue-500/20 px-4 py-2 rounded-lg mb-4">
                    <p class="text-blue-300 font-semibold">{{ __('deckvaluation.thanks_check_email') }}</p>
                </div>
                
                <p class="text-gray-300 text-lg mb-4">
                    {{ __('deckvaluation.thanks_email_sent') }}
                </p>

                <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-6 mb-6">
                    <p class="text-blue-200 text-sm mb-2">
                        <strong>{{ __('deckvaluation.thanks_email_to') }}</strong>
                    </p>
                    <p class="text-white text-lg font-mono">{{ $email }}</p>
                </div>

                <div class="space-y-3 text-left">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-white font-medium">{{ __('deckvaluation.thanks_feature1_title') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('deckvaluation.thanks_feature1_desc') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-white font-medium">{{ __('deckvaluation.thanks_feature2_title') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('deckvaluation.thanks_feature2_desc') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-white font-medium">{{ __('deckvaluation.thanks_feature3_title') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('deckvaluation.thanks_feature3_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deck Info -->
            <div class="border-t border-white/10 pt-6 mt-6">
                <div class="bg-black/30 rounded-lg p-4">
                    <p class="text-gray-400 text-sm mb-1">{{ __('deckvaluation.thanks_deck_name') }}</p>
                    <p class="text-white text-xl font-semibold">{{ $deckName }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Access (if still in session) -->
        @if($quickAccessLink)
        <div class="bg-yellow-900/20 border border-yellow-500/30 rounded-xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="bg-yellow-500/20 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-yellow-200 font-semibold mb-2">{{ __('deckvaluation.thanks_quick_title') }}</p>
                    <p class="text-yellow-100/80 text-sm mb-3">{{ __('deckvaluation.thanks_quick_desc') }}</p>
                    <a href="{{ $quickAccessLink }}" class="inline-block bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-medium transition">
                        {{ __('deckvaluation.thanks_quick_button') }}
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Additional Actions -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('pokemon.deck-valuation.step1') }}" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition text-center">
                {{ __('deckvaluation.thanks_value_another') }}
            </a>
            @guest
            <a href="{{ route('register') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition text-center">
                {{ __('deckvaluation.thanks_create_account') }}
            </a>
            @endguest
        </div>

        <!-- Help Text -->
        <div class="text-center mt-8">
            <p class="text-gray-400 text-sm">
                {{ __('deckvaluation.thanks_help_text') }}
                <a href="{{ route('support.index') }}" class="text-blue-400 hover:text-blue-300 underline">{{ __('deckvaluation.thanks_contact_support') }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
