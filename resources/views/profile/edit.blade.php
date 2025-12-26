@extends('layouts.app')

@section('page_title', __('messages.Edit Profile'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Profile Information -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">{{ __('messages.Edit_Profile') }}</h2>
        
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <x-input-label for="name" :value="__('messages.name')" class="text-gray-300" />
                <x-input id="name" name="name" type="text" class="mt-1 w-full bg-black/50 border-white/20 text-white" :value="old('name', $user->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div class="mb-4">
                <x-input-label for="email" :value="__('messages.email')" class="text-gray-300" />
                <x-input id="email" name="email" type="email" class="mt-1 w-full bg-black/50 border-white/20 text-white" :value="old('email', $user->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div class="mb-4">
                <x-input-label for="password" :value="__('messages.password_optional')" class="text-gray-300" />
                <x-input id="password" name="password" type="password" class="mt-1 w-full bg-black/50 border-white/20 text-white" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="mb-4">
                <x-input-label for="password_confirmation" :value="__('messages.password_confirmation')" class="text-gray-300" />
                <x-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full bg-black/50 border-white/20 text-white" autocomplete="new-password" />
            </div>
            <div class="flex justify-end">
                <x-button type="submit">{{ __('messages.Save') }}</x-button>
            </div>
        </form>
    </div>

    <!-- Game Preferences -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-2">Active Games</h2>
        <p class="text-gray-400 text-sm mb-6">Select which card games you want to use in your collection and decks.</p>
        
        <form method="POST" action="{{ route('profile.games.update') }}">
            @csrf
            
            <div class="space-y-4">
                @foreach($allGames as $game)
                    <label class="flex items-center p-4 bg-black/30 hover:bg-black/40 rounded-lg cursor-pointer transition-colors border border-white/10 hover:border-white/20">
                        <input 
                            type="checkbox" 
                            name="games[]" 
                            value="{{ $game->id }}"
                            {{ in_array($game->id, $userGames) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-500 bg-black/50 border-white/30 rounded focus:ring-blue-500 focus:ring-2"
                        >
                        <div class="ml-4 flex-1">
                            <div class="text-white font-semibold">{{ $game->name }}</div>
                            <div class="text-gray-400 text-sm">Code: {{ $game->code }} | TCGCSV Category: {{ $game->tcgcsv_category_id }}</div>
                        </div>
                        @if(in_array($game->id, $userGames))
                            <span class="ml-auto px-3 py-1 bg-green-500/20 text-green-400 text-xs font-semibold rounded-full">Active</span>
                        @endif
                    </label>
                @endforeach
            </div>

            <div class="flex justify-end mt-6">
                <x-button type="submit">Save Game Preferences</x-button>
            </div>
        </form>

        @if(session('status') === 'games-updated')
            <div class="mt-4 p-4 bg-green-500/20 border border-green-500/30 rounded-lg">
                <p class="text-green-400 text-sm">✓ Game preferences updated successfully!</p>
            </div>
        @endif
    </div>

</div>
@endsection
