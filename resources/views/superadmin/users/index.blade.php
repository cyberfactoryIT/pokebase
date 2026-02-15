@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-2xl text-white">All Users</h2>
                    <p class="text-gray-400 text-sm">List of registered users (most recent first)</p>
                </div>
                <div>
                    <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg">Back to Dashboard</a>
                </div>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-white/5">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Organization</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach($users as $user)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-white font-medium">{{ $user->name }}</p>
                                    <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-300">
                                @if($user->organization)
                                    <div class="flex flex-col">
                                        <span class="font-medium text-white">{{ $user->organization->name }}</span>
                                        <div class="mt-1">
                                            @if($user->organization->isOnTrial())
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-600 text-black">Trial until {{ $user->organization->trial_expires_at->format('Y-m-d') }}</span>
                                            @elseif(!empty($user->organization->promotion_code))
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white">Promo: {{ $user->organization->promotion_code }}</span>
                                            @elseif(!empty($user->organization->trial_promotion_id))
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-600 text-white">Trial promo applied</span>
                                            @elseif($user->organization->trial_expires_at)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-600 text-white">Trial expired {{ $user->organization->trial_expires_at->format('Y-m-d') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
@endsection
