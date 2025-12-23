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
            <h1 class="text-3xl font-bold text-white mb-2">Edit Deck</h1>
            <p class="text-gray-400 mb-8">Update your deck details</p>

            <form method="POST" action="{{ route('decks.update', $deck) }}">
                @csrf
                @method('PUT')

                <!-- Deck Name -->
                <div class="mb-6">
                    <label for="name" class="block text-white font-medium mb-2">
                        Deck Name <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $deck->name) }}"
                        required
                        class="w-full px-4 py-3 bg-black/50 border border-white/20 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition"
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
                        value="{{ old('format', $deck->format) }}"
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
                    >{{ old('description', $deck->description) }}</textarea>
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
                        Update Deck
                    </button>
                    <a 
                        href="{{ route('decks.show', $deck) }}"
                        class="px-6 py-3 bg-white/10 hover:bg-white/20 text-gray-300 font-medium rounded-lg transition text-center"
                    >
                        Cancel
                    </a>
                </div>
            </form>

            <!-- Delete Deck -->
            <div class="mt-8 pt-8 border-t border-white/10">
                <h3 class="text-red-400 font-semibold mb-2">Danger Zone</h3>
                <p class="text-gray-400 text-sm mb-4">Deleting a deck is permanent and cannot be undone.</p>
                <form method="POST" action="{{ route('decks.destroy', $deck) }}" onsubmit="return confirm('Are you sure you want to delete this deck? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        Delete Deck
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
