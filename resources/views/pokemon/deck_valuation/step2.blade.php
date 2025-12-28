@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Pokémon Deck Valuation</h1>
            <p class="text-gray-400">Get your deck valuation results</p>
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
                    <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">2</div>
                    <div class="w-16 h-1 bg-gray-600"></div>
                    <div class="bg-gray-600 text-white rounded-full w-10 h-10 flex items-center justify-center">3</div>
                </div>
            </div>
            <div class="flex justify-between max-w-md mx-auto mt-2">
                <span class="text-green-400 text-sm">Add Cards</span>
                <span class="text-blue-400 font-semibold text-sm">Your Info</span>
                <span class="text-gray-500 text-sm">Valuation</span>
            </div>
        </div>

        <!-- Lead Capture Form -->
        <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-white mb-2">Almost there!</h2>
                <p class="text-gray-400">Enter your information to receive your deck valuation.</p>
            </div>

            <form method="POST" action="{{ route('pokemon.deck-valuation.submit') }}" class="space-y-6">
                @csrf

                <!-- Deck Name -->
                <div>
                    <label for="deck_name" class="block text-sm font-medium text-gray-300 mb-2">
                        Deck Name <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="deck_name" 
                        name="deck_name" 
                        required
                        value="{{ old('deck_name') }}"
                        placeholder="My Pikachu Deck"
                        class="w-full px-4 py-3 bg-black/50 border border-white/20 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    @error('deck_name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                        Email Address <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="{{ old('email') }}"
                        placeholder="your@email.com"
                        class="w-full px-4 py-3 bg-black/50 border border-white/20 text-white placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Marketing Consent -->
                <div class="flex items-start">
                    <input 
                        type="checkbox" 
                        id="consent_marketing" 
                        name="consent_marketing"
                        value="1"
                        {{ old('consent_marketing') ? 'checked' : '' }}
                        class="mt-1 h-4 w-4 bg-black/50 border-white/20 rounded text-blue-600 focus:ring-2 focus:ring-blue-500"
                    >
                    <label for="consent_marketing" class="ml-3 text-sm text-gray-300">
                        I'd like to receive updates, tips, and exclusive offers about Pokémon card collecting
                    </label>
                </div>

                <!-- Privacy Notice -->
                <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4">
                    <p class="text-blue-200 text-sm">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Your information is secure and will never be shared with third parties. 
                        <a href="{{ route('privacy') }}" class="underline hover:text-blue-100">Privacy Policy</a>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-4">
                    <a 
                        href="{{ route('pokemon.deck-valuation.step1') }}" 
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition text-center"
                    >
                        ← Back
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition"
                    >
                        Get My Valuation →
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
