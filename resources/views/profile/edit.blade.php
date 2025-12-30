@extends('layouts.app')

@section('page_title', __('profile/edit.page_title'))

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Error Messages -->
    @if(session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-400/30 text-red-300 px-4 py-3 rounded-lg flex items-start gap-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1">
                <p class="font-semibold">{{ session('error') }}</p>
                @if(session('error_detail'))
                    <p class="text-sm mt-1">{{ session('error_detail') }}</p>
                @endif
            </div>
            <button @click="show = false" class="text-red-300 hover:text-red-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif
    
    <!-- Tab Navigation -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
        <div class="flex border-b border-white/10">
            <a href="{{ route('profile.edit') }}" 
               class="px-6 py-4 text-white transition border-b-2 border-blue-500">
                <i class="fa fa-user mr-2"></i>{{ __('profile/edit.tab_profile') }}
            </a>
            <a href="{{ route('profile.subscription') }}" 
               class="px-6 py-4 text-gray-400 hover:text-white transition border-b-2 border-transparent">
                <i class="fa fa-credit-card mr-2"></i>{{ __('profile/edit.tab_subscription') }}
            </a>
            <a href="{{ route('profile.transactions') }}" 
               class="px-6 py-4 text-gray-400 hover:text-white transition border-b-2 border-transparent">
                <i class="fa fa-receipt mr-2"></i>{{ __('profile/edit.tab_transactions') }}
            </a>
        </div>
    </div>

    <div class="space-y-6">
    
    <!-- Profile Information -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-6">{{ __('profile/edit.edit_profile') }}</h2>
        
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <x-input-label for="name" :value="__('profile/edit.name')" class="text-gray-300" />
                <x-input id="name" name="name" type="text" class="mt-1 w-full bg-black/50 border-white/20 text-white" :value="old('name', $user->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div class="mb-4">
                <x-input-label for="email" :value="__('profile/edit.email')" class="text-gray-300" />
                <x-input id="email" name="email" type="email" class="mt-1 w-full bg-black/50 border-white/20 text-white" :value="old('email', $user->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            
            <div class="mb-4">
                <x-input-label for="preferred_currency" :value="__('profile/edit.preferred_currency')" class="text-gray-300" />
                <select id="preferred_currency" name="preferred_currency" class="mt-1 w-full bg-black/50 border-white/20 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <option value="">{{ __('profile/edit.use_default_currency') }}</option>
                    <option value="EUR" {{ old('preferred_currency', $user->preferred_currency) === 'EUR' ? 'selected' : '' }}>🇪🇺 EUR - Euro</option>
                    <option value="USD" {{ old('preferred_currency', $user->preferred_currency) === 'USD' ? 'selected' : '' }}>🇺🇸 USD - US Dollar</option>
                    <option value="GBP" {{ old('preferred_currency', $user->preferred_currency) === 'GBP' ? 'selected' : '' }}>🇬🇧 GBP - British Pound</option>
                    <option value="DKK" {{ old('preferred_currency', $user->preferred_currency) === 'DKK' ? 'selected' : '' }}>🇩🇰 DKK - Danish Krone</option>
                    <option value="SEK" {{ old('preferred_currency', $user->preferred_currency) === 'SEK' ? 'selected' : '' }}>🇸🇪 SEK - Swedish Krona</option>
                    <option value="NOK" {{ old('preferred_currency', $user->preferred_currency) === 'NOK' ? 'selected' : '' }}>🇳🇴 NOK - Norwegian Krone</option>
                    <option value="CHF" {{ old('preferred_currency', $user->preferred_currency) === 'CHF' ? 'selected' : '' }}>🇨🇭 CHF - Swiss Franc</option>
                    <option value="JPY" {{ old('preferred_currency', $user->preferred_currency) === 'JPY' ? 'selected' : '' }}>🇯🇵 JPY - Japanese Yen</option>
                    <option value="CAD" {{ old('preferred_currency', $user->preferred_currency) === 'CAD' ? 'selected' : '' }}>🇨🇦 CAD - Canadian Dollar</option>
                    <option value="AUD" {{ old('preferred_currency', $user->preferred_currency) === 'AUD' ? 'selected' : '' }}>🇦🇺 AUD - Australian Dollar</option>
                </select>
                <p class="mt-1 text-sm text-gray-400">{{ __('profile/edit.preferred_currency_description') }}</p>
                <x-input-error :messages="$errors->get('preferred_currency')" class="mt-2" />
            </div>
            
            <div class="mb-4">
                <x-input-label for="password" :value="__('profile/edit.password_optional')" class="text-gray-300" />
                <x-input id="password" name="password" type="password" class="mt-1 w-full bg-black/50 border-white/20 text-white" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="mb-4">
                <x-input-label for="password_confirmation" :value="__('profile/edit.password_confirmation')" class="text-gray-300" />
                <x-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full bg-black/50 border-white/20 text-white" autocomplete="new-password" />
            </div>
            <div class="flex justify-end">
                <x-button type="submit">{{ __('profile/edit.save') }}</x-button>
            </div>
        </form>
    </div>

    <!-- Game Preferences -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">{{ __('profile/edit.active_games') }}</h2>
                <p class="text-gray-400 text-sm">{{ __('profile/edit.active_games_description') }}</p>
            </div>
            
            <!-- Membership tier badge and limit info -->
            <div class="text-right">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-500/20 text-blue-300 text-sm font-semibold rounded-lg mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    {{ __('games.tier.' . $user->subscriptionTier()) }}
                </div>
                <p class="text-gray-400 text-xs">
                    {{ __('games.limits.' . $user->subscriptionTier()) }}
                </p>
            </div>
        </div>
        
        <!-- Game limit warning (shown when at or near limit) -->
        @php
            $maxGames = $user->maxActiveGames();
            $currentCount = count($userGames);
            $isAtLimit = $maxGames !== null && $currentCount >= $maxGames;
            $isNearLimit = $maxGames !== null && $currentCount >= ($maxGames - 1) && $currentCount < $maxGames;
        @endphp
        
        @if($isAtLimit || $isNearLimit)
            <div class="mb-6 p-4 {{ $isAtLimit ? 'bg-red-500/10 border-red-500/30' : 'bg-yellow-500/10 border-yellow-500/30' }} border rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 {{ $isAtLimit ? 'text-red-400' : 'text-yellow-400' }} flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold {{ $isAtLimit ? 'text-red-300' : 'text-yellow-300' }} mb-1">
                            {{ __('games.limit.reached.title') }}
                        </p>
                        <p class="text-sm {{ $isAtLimit ? 'text-red-200' : 'text-yellow-200' }} mb-3">
                            {{ __('games.limit.reached.' . ($isAtLimit ? 'body_at_limit' : 'body'), [
                                'tier' => __('games.tier.' . $user->subscriptionTier()),
                                'max' => $maxGames,
                            ]) }}
                        </p>
                        @if(!$user->isPremium())
                            <a href="{{ route('profile.subscription') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('games.limit.cta_upgrade') }}
                            </a>
                            <p class="text-xs {{ $isAtLimit ? 'text-red-300' : 'text-yellow-300' }} mt-2">
                                {{ __('games.limit.upgrade_benefits') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        <form method="POST" action="{{ route('profile.games.update') }}" x-data="{ 
            selectedGames: {{ json_encode($userGames) }}, 
            defaultGame: {{ $user->default_game_id ?? 'null' }},
            maxGames: {{ $maxGames ?? 'null' }},
            showLimitWarning: false,
            checkLimit() {
                if (this.maxGames !== null && this.selectedGames.length > this.maxGames) {
                    this.showLimitWarning = true;
                    return false;
                }
                this.showLimitWarning = false;
                return true;
            }
        }">
            @csrf
            
            <!-- Dynamic limit warning -->
            <div x-show="showLimitWarning" x-cloak class="mb-4 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                <p class="text-red-300 text-sm">
                    {{ __('games.limit.reached.body', [
                        'tier' => __('games.tier.' . $user->subscriptionTier()),
                        'max' => $maxGames ?? 0,
                    ]) }}
                </p>
            </div>
            
            <div class="space-y-4">
                @foreach($allGames as $game)
                    <label class="flex items-center p-4 bg-black/30 hover:bg-black/40 rounded-lg cursor-pointer transition-colors border border-white/10 hover:border-white/20">
                        <input 
                            type="checkbox" 
                            name="games[]" 
                            value="{{ $game->id }}"
                            {{ in_array($game->id, $userGames) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-500 bg-black/50 border-white/30 rounded focus:ring-blue-500 focus:ring-2"
                            x-model="selectedGames"
                            @change="if (!selectedGames.includes({{ $game->id }}) && defaultGame === {{ $game->id }}) { defaultGame = null }; checkLimit();"
                        >
                        <div class="ml-4 flex-1">
                            <div class="text-white font-semibold">{{ $game->name }}</div>
                            <div class="text-gray-400 text-sm">{{ __('profile/edit.game_code') }}: {{ $game->code }} | {{ __('profile/edit.game_tcgcsv') }}: {{ $game->tcgcsv_category_id }}</div>
                        </div>
                        @if(in_array($game->id, $userGames))
                            <span class="ml-auto px-3 py-1 bg-green-500/20 text-green-400 text-xs font-semibold rounded-full">{{ __('profile/edit.active_badge') }}</span>
                        @endif
                    </label>
                @endforeach
            </div>

            <!-- Default Game Selection -->
            <div class="mt-8 pt-6 border-t border-white/10" x-show="selectedGames.length > 0">
                <h3 class="text-xl font-bold text-white mb-2">{{ __('profile/edit.default_game') }}</h3>
                <p class="text-gray-400 text-sm mb-4">{{ __('profile/edit.default_game_description') }}</p>
                
                <div class="space-y-3">
                    @foreach($allGames as $game)
                        <label 
                            class="flex items-center p-3 bg-black/20 hover:bg-black/30 rounded-lg cursor-pointer transition-colors border border-white/10 hover:border-white/20"
                            x-show="selectedGames.includes({{ $game->id }})"
                        >
                            <input 
                                type="radio" 
                                name="default_game_id" 
                                value="{{ $game->id }}"
                                {{ $user->default_game_id == $game->id ? 'checked' : '' }}
                                class="w-4 h-4 text-yellow-500 bg-black/50 border-white/30 focus:ring-yellow-500 focus:ring-2"
                                x-model="defaultGame"
                            >
                            <div class="ml-3 flex items-center gap-2">
                                <span class="text-white font-medium">{{ $game->name }}</span>
                                <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs font-semibold rounded">{{ __('profile/edit.default_badge') }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <x-button type="submit">{{ __('profile/edit.save_game_preferences') }}</x-button>
            </div>
        </form>

        @if(session('status') === 'games-updated')
            <div class="mt-4 p-4 bg-green-500/20 border border-green-500/30 rounded-lg">
                <p class="text-green-400 text-sm">{{ __('profile/edit.games_updated') }}</p>
            </div>
        @endif
    </div>

    </div>
</div>
@endsection
