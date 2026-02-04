@props(['organization'])

@php
    $isOnTrial = $organization->isOnTrial();
    $hasExpiredTrial = $organization->hasExpiredTrial();
    
    if (!$isOnTrial && !$hasExpiredTrial) {
        return; // Don't show banner if not relevant
    }
    
    $daysLeft = $isOnTrial ? now()->diffInDays($organization->trial_expires_at, false) : 0;
    $showUrgent = $daysLeft <= 3 && $daysLeft >= 0;
@endphp

@if($isOnTrial)
    <!-- Active Trial Banner -->
    <div class="mb-6 rounded-lg overflow-hidden {{ $showUrgent ? 'bg-gradient-to-r from-orange-600 to-red-600' : 'bg-gradient-to-r from-blue-600 to-purple-600' }} shadow-lg">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    @if($showUrgent)
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    @if($showUrgent)
                        <h3 class="text-xl font-bold text-white mb-2">
                            {{ __('trial.banner.urgent_title', ['days' => $daysLeft]) }}
                        </h3>
                        <p class="text-white/90 mb-4">
                            {{ __('trial.banner.urgent_message', [
                                'plan' => $organization->trialPlan->name,
                                'date' => $organization->trial_expires_at->format('d/m/Y')
                            ]) }}
                        </p>
                    @else
                        <h3 class="text-xl font-bold text-white mb-2">
                            {{ __('trial.banner.active_title') }}
                        </h3>
                        <p class="text-white/90 mb-4">
                            {{ __('trial.banner.active_message', [
                                'plan' => $organization->trialPlan->name,
                                'days' => $daysLeft,
                                'date' => $organization->trial_expires_at->format('d/m/Y')
                            ]) }}
                        </p>
                    @endif
                    
                    <a href="{{ route('pages.pricing') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition shadow-lg">
                        {{ __('trial.banner.upgrade_now') }}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
                <button @click="$el.closest('[x-data]').style.display='none'" class="flex-shrink-0 text-white/70 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
@elseif($hasExpiredTrial)
    <!-- Expired Trial Banner -->
    <div class="mb-6 rounded-lg overflow-hidden bg-gradient-to-r from-gray-700 to-gray-800 border-2 border-yellow-500/50 shadow-lg" x-data="{ show: true }" x-show="show">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-white mb-2">
                        {{ __('trial.banner.expired_title') }}
                    </h3>
                    <p class="text-gray-300 mb-4">
                        {{ __('trial.banner.expired_message') }}
                    </p>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('pages.pricing') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg transition shadow-lg">
                            {{ __('trial.banner.subscribe_now') }}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                        <button @click="show = false" class="px-4 py-2 text-gray-400 hover:text-white transition">
                            {{ __('trial.banner.dismiss') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
