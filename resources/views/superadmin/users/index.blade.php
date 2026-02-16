@extends('layouts.app')

@section('content')

<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-3xl text-white mb-2">
                        <i class="fa fa-users text-blue-400 mr-2"></i>
                        All Users
                    </h2>
                    <p class="text-gray-400">
                        Complete list of registered users
                    </p>
                </div>
                
                <!-- Back to Dashboard -->
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-medium rounded-lg transition border border-white/20">
                    <i class="fa fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>

            <!-- Stats Summary -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-900/30 border border-blue-500/30 rounded-lg p-4">
                    <p class="text-blue-300 text-sm mb-1">Total Users</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($users->total()) }}</p>
                </div>
                <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4">
                    <p class="text-green-300 text-sm mb-1">This Page</p>
                    <p class="text-2xl font-bold text-white">{{ $users->count() }}</p>
                </div>
                <div class="bg-purple-900/30 border border-purple-500/30 rounded-lg p-4">
                    <p class="text-purple-300 text-sm mb-1">Current Page</p>
                    <p class="text-2xl font-bold text-white">{{ $users->currentPage() }}</p>
                </div>
                <div class="bg-yellow-900/30 border border-yellow-500/30 rounded-lg p-4">
                    <p class="text-yellow-300 text-sm mb-1">Total Pages</p>
                    <p class="text-2xl font-bold text-white">{{ $users->lastPage() }}</p>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white/5 border border-white/10 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-white/5">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Email Verified
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Organization
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Total Cards
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Attività
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    Registered
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @forelse($users as $user)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    #{{ $user->id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-white font-medium">{{ $user->name }}</p>
                                        <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-green-600/80 text-white" title="Verified {{ $user->email_verified_at->format('Y-m-d H:i') }}">
                                            <i class="fa fa-check-circle"></i> Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-amber-600/80 text-white" title="Email not verified">
                                            <i class="fa fa-exclamation-circle"></i> Unverified
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->organization)
                                        <span class="text-gray-300">{{ $user->organization->name }}</span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->organization && $user->organization->pricingPlan)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-purple-600 text-white">
                                            {{ $user->organization->pricingPlan->name }}
                                        </span>
                                    @elseif($user->organization && $user->organization->isOnTrial())
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-600 text-black">
                                            Trial ({{ $user->organization->trialPlan->name ?? 'Unknown' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-600 text-white">
                                            Free
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->organization)
                                        <div class="flex flex-col gap-1">
                                            @if($user->organization->isOnTrial())
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-600 text-black">
                                                    Trial until {{ $user->organization->trial_expires_at->format('Y-m-d') }}
                                                </span>
                                            @endif
                                            
                                            @if(!empty($user->organization->promotion_code))
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white">
                                                    Promo: {{ $user->organization->promotion_code }}
                                                </span>
                                            @endif
                                            
                                            @if(!empty($user->organization->trial_promotion_id) && !$user->organization->isOnTrial() && empty($user->organization->promotion_code))
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-600 text-white">
                                                    Trial promo applied
                                                </span>
                                            @endif
                                            
                                            @if($user->organization->trial_expires_at && $user->organization->trial_expires_at->isPast() && !$user->organization->pricingPlan)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-600 text-white">
                                                    Trial expired {{ $user->organization->trial_expires_at->format('Y-m-d') }}
                                                </span>
                                            @endif
                                            
                                            @if(!$user->organization->isOnTrial() && empty($user->organization->promotion_code) && empty($user->organization->trial_promotion_id) && !$user->organization->trial_expires_at)
                                                <span class="text-gray-500 text-sm">-</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-300 font-mono">{{ number_format($user->total_cards ?? 0) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        @if($user->last_activity)
                                            <p class="text-gray-300 text-sm" title="{{ \Carbon\Carbon::createFromTimestamp($user->last_activity)->format('Y-m-d H:i:s') }}">
                                                {{ \Carbon\Carbon::createFromTimestamp($user->last_activity)->format('Y-m-d H:i') }}
                                            </p>
                                            <p class="text-gray-500 text-xs">{{ \Carbon\Carbon::createFromTimestamp($user->last_activity)->diffForHumans() }}</p>
                                        @else
                                            <span class="text-gray-500 text-sm">-</span>
                                        @endif
                                        <p class="text-gray-400 text-xs mt-1">{{ number_format($user->login_count ?? 0) }} login</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-gray-300 text-sm">{{ $user->created_at->format('Y-m-d H:i') }}</p>
                                        <p class="text-gray-500 text-xs">{{ $user->created_at->diffForHumans() }}</p>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    No users found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="mt-6">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
