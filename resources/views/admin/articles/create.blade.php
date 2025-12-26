@extends('layouts.app')

@section('page_title', 'Create Article')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Create New Article</h2>
                <x-button as="a" href="{{ route('admin.articles.index') }}" variant="neutral">
                    Back to List
                </x-button>
            </div>

            <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-6">
                    <!-- Game Selection -->
                    <div>
                        <label for="game_id" class="block text-sm font-medium text-gray-700 mb-1">Game *</label>
                        <select name="game_id" id="game_id" required class="w-full rounded-md border-gray-300 @error('game_id') border-red-500 @enderror">
                            <option value="">Select a game</option>
                            @foreach($games as $game)
                                <option value="{{ $game->id }}" {{ old('game_id') == $game->id ? 'selected' : '' }}>
                                    {{ $game->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('game_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Original Language -->
                    <div>
                        <label for="original_locale" class="block text-sm font-medium text-gray-700 mb-1">Original Language *</label>
                        <select name="original_locale" id="original_locale" required class="w-full rounded-md border-gray-300 @error('original_locale') border-red-500 @enderror">
                            <option value="en" {{ old('original_locale', 'en') == 'en' ? 'selected' : '' }}>English (EN)</option>
                            <option value="it" {{ old('original_locale') == 'it' ? 'selected' : '' }}>Italian (IT)</option>
                            <option value="da" {{ old('original_locale') == 'da' ? 'selected' : '' }}>Danish (DA)</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Language you're writing in (will auto-translate to others)</p>
                        @error('original_locale')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <input list="categories" name="category" id="category" value="{{ old('category') }}" required 
                            class="w-full rounded-md border-gray-300 @error('category') border-red-500 @enderror"
                            placeholder="e.g. Getting Started, Rules, Collecting...">
                        <datalist id="categories">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                        @error('category')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required 
                            class="w-full rounded-md border-gray-300 @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt (Short Description) *</label>
                        <textarea name="excerpt" id="excerpt" rows="2" required 
                            class="w-full rounded-md border-gray-300 @error('excerpt') border-red-500 @enderror">{{ old('excerpt') }}</textarea>
                        <p class="text-sm text-gray-500 mt-1">Brief summary shown on cards (1-2 sentences)</p>
                        @error('excerpt')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Body -->
                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Body (Markdown) *</label>
                        <textarea name="body" id="body" rows="12" required 
                            class="w-full rounded-md border-gray-300 font-mono text-sm @error('body') border-red-500 @enderror">{{ old('body') }}</textarea>
                        <p class="text-sm text-gray-500 mt-1">
                            Supports: ## Headers, **bold**, *italic*, [links](url), - bullet lists
                        </p>
                        @error('body')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image (optional, max 1MB)</label>
                        <input type="file" name="image" id="image" accept="image/*" 
                            class="w-full rounded-md border-gray-300 @error('image') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Small decorative image (will be resized)</p>
                        @error('image')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- External URL -->
                    <div>
                        <label for="external_url" class="block text-sm font-medium text-gray-700 mb-1">External URL (optional)</label>
                        <input type="url" name="external_url" id="external_url" value="{{ old('external_url') }}" 
                            placeholder="https://example.com"
                            class="w-full rounded-md border-gray-300 @error('external_url') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Link to external source (opens in new tab)</p>
                        @error('external_url')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sort Order -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order') }}" 
                                class="w-full rounded-md border-gray-300 @error('sort_order') border-red-500 @enderror"
                                placeholder="Leave empty for automatic">
                            @error('sort_order')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                            <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}" 
                                class="w-full rounded-md border-gray-300 @error('published_at') border-red-500 @enderror">
                            @error('published_at')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Published Checkbox -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_published" id="is_published" value="1" 
                            {{ old('is_published', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600">
                        <label for="is_published" class="ml-2 text-sm text-gray-700">Publish immediately</label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-3 pt-4 border-t">
                        <x-button type="submit" variant="primary">Create Article</x-button>
                        <x-button as="a" href="{{ route('admin.articles.index') }}" variant="neutral">Cancel</x-button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
