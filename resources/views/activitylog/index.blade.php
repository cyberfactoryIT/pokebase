@extends('layouts.app')

@section('page_title', __('Activity Log'))

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ __('messages.activity_log') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <x-table>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('messages.date') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.user') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.action') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Esempio statico, sostituisci con ciclo su $activities --}}
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700">2025-09-14 10:23</td>
                            <td class="px-4 py-2 text-gray-700">Mario Rossi</td>
                            <td class="px-4 py-2 text-gray-700">{{ __('messages.login') }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ __('messages.access_performed') }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700">2025-09-14 10:25</td>
                            <td class="px-4 py-2 text-gray-700">Anna Bianchi</td>
                            <td class="px-4 py-2 text-gray-700">{{ __('messages.profile_update') }}</td>
                            <td class="px-4 py-2 text-gray-700">{{ __('messages.email_updated') }}</td>
                        </tr>
                    </tbody>
                </x-table>
            </div>
        </x-card>
    </div>
</div>
@endsection
