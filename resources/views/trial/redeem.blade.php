<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('trial.redeem_code') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            
            @if($hasSubscription)
                <!-- Already has subscription -->
                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-6 mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-yellow-400">{{ __('trial.already_subscribed_title') }}</h3>
                    </div>
                    <p class="text-gray-300">{{ __('trial.already_subscribed_message') }}</p>
                    <a href="{{ route('billing.index') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        {{ __('trial.view_subscription') }}
                    </a>
                </div>
            @elseif($isOnTrial)
                <!-- Already on trial -->
                <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-6 mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-green-400">{{ __('trial.active_trial_title') }}</h3>
                    </div>
                    <p class="text-gray-300 mb-2">
                        {{ __('trial.active_trial_message', [
                            'plan' => $organization->trialPlan->name,
                            'expires' => $organization->trial_expires_at->format('d/m/Y H:i')
                        ]) }}
                    </p>
                    <p class="text-sm text-gray-400 mt-4">{{ __('trial.cannot_use_another_code') }}</p>
                </div>
            @else
                <!-- Redeem form -->
                <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                        <h3 class="text-2xl font-bold text-white mb-2">{{ __('trial.form_title') }}</h3>
                        <p class="text-blue-100">{{ __('trial.form_subtitle') }}</p>
                    </div>
                    
                    <div class="p-6">
                        @if(session('success'))
                            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-6">
                                <p class="text-green-400">{{ session('success') }}</p>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('trial.redeem') }}">
                            @csrf
                            
                            <div class="mb-6">
                                <label for="code" class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ __('trial.code_label') }}
                                </label>
                                <input 
                                    type="text" 
                                    id="code" 
                                    name="code" 
                                    value="{{ old('code') }}"
                                    placeholder="{{ __('trial.code_placeholder') }}"
                                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white text-lg font-mono uppercase focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    maxlength="50"
                                    required
                                    autofocus
                                >
                                @error('code')
                                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-6">
                                <p class="text-sm text-blue-300">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ __('trial.info_message') }}
                                </p>
                            </div>
                            
                            <button 
                                type="submit" 
                                class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg transition"
                            >
                                {{ __('trial.redeem_button') }}
                            </button>
                        </form>
                        
                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <p class="text-sm text-gray-400 text-center">
                                {{ __('trial.no_code_question') }}
                                <a href="{{ route('pages.pricing') }}" class="text-blue-400 hover:text-blue-300">
                                    {{ __('trial.view_plans') }}
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>
