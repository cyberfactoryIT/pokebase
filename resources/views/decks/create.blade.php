@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-6">
        <div class="mb-6">
            <a href="{{ route('decks.index') }}" class="text-gray-400 hover:text-white transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Decks
            </a>
        </div>

        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <h1 class="text-3xl font-bold text-white mb-2">Create New Deck</h1>
            <p class="text-gray-400 mb-8">Build a new deck for your collection</p>

            <form method="POST" action="{{ route('decks.store') }}">
                @csrf

                <!-- Deck Name -->
                <div class="mb-6">
                    <label for="name" class="block text-white font-medium mb-2">
                        Deck Name <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        required
                        class="w-full px-4 py-3 bg-black/50 border border-white/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition"
                        placeholder="e.g., Charizard Deck"
                    >
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Format -->
                <div class="mb-6">
                    <label for="format" class="block text-white font-medium mb-2">
                        Format
                    </label>
                    <input 
                        type="text" 
                        id="format" 
                        name="format" 
                        value="{{ old('format') }}"
                        class="w-full px-4 py-3 bg-black/50 border border-white/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition"
                        placeholder="e.g., Standard, Expanded, Unlimited"
                    >
                    @error('format')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <label for="description" class="block text-white font-medium mb-2">
                        Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="w-full px-4 py-3 bg-black/50 border border-white/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition resize-none"
                        placeholder="Describe your deck strategy..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition"
                    >
                        Create Deck
                    </button>
                    <a 
                        href="{{ route('decks.index') }}"
                        class="px-6 py-3 bg-white/10 hover:bg-white/20 text-gray-300 font-medium rounded-lg transition text-center"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
