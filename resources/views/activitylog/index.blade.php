@extends('layouts.app')

@section('page_title', __('Activity Log'))

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ __('messages.activity_log') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-black/30">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-300">{{ __('messages.date') }}</th>
                            <th class="px-4 py-2 text-left text-gray-300">{{ __('messages.user') }}</th>
                            <th class="px-4 py-2 text-left text-gray-300">{{ __('messages.action') }}</th>
                            <th class="px-4 py-2 text-left text-gray-300">{{ __('messages.details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Esempio statico, sostituisci con ciclo su $activities --}}
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <td class="px-4 py-2 text-gray-300">2025-09-14 10:23</td>
                            <td class="px-4 py-2 text-gray-300">Mario Rossi</td>
                            <td class="px-4 py-2 text-gray-300">{{ __('messages.login') }}</td>
                            <td class="px-4 py-2 text-gray-300">{{ __('messages.access_performed') }}</td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <td class="px-4 py-2 text-gray-300">2025-09-14 10:25</td>
                            <td class="px-4 py-2 text-gray-300">Anna Bianchi</td>
                            <td class="px-4 py-2 text-gray-300">{{ __('messages.profile_update') }}</td>
                            <td class="px-4 py-2 text-gray-300">{{ __('messages.email_updated') }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
