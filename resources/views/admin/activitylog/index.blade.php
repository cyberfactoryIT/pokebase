@extends('layouts.app')

@section('content')

<x-card>
    <h2 class="text-2xl font-bold mb-6">{{ __('messages.activity_log') }}</h2>
        <form method="GET" class="mb-6 flex flex-wrap gap-3 items-center">
        <x-select name="type" :options="$types" placeholder="All types" :value="request('type')" />
        @if(config('organizations.enabled') && !$isAdmin)
            <x-input name="organization_id" :value="request('organization_id')" placeholder="{{ __('messages.organization_id') }}" />
        @endif
        <x-button type="submit" icon="filter">{{ __('messages.filter') }}</x-button>
    </form>
    <div class="overflow-x-auto">
        <x-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2">{{ __('messages.type') }}</th>
                    <th class="px-4 py-2">{{ __('messages.action') }}</th>
                    @if(config('organizations.enabled') && !$isAdmin)
                        <th class="px-4 py-2">{{ __('messages.organization') }}</th>
                    @endif
                    <th class="px-4 py-2">{{ __('messages.user') }}</th>
                    <th class="px-4 py-2">{{ __('messages.data') }}</th>
                    <th class="px-4 py-2">{{ __('messages.date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr class="border-b hover:bg-gray-50">
                    <td>
                        <x-badge variant="primary">{{ __('logmessages.type.'.$log->type) }}</x-badge>
                    </td>
                    <td>
                        <x-badge variant="neutral">{{ __('logmessages.action.'.$log->action) }}</x-badge>
                    </td>
                    @if(config('organizations.enabled') && !$isAdmin)
                        <td>{{ $log->organization_id }} - {{ $organizations[$log->organization_id] ?? '-' }}</td>
                    @endif
                    <td>
                        @php
                            $user = $log->user_id ? \App\Models\User::find($log->user_id) : null;
                        @endphp
                        {{ $user ? $user->name : '-' }}
                    </td>
                    <td class="text-xs">
                        @if(is_array($log->data))
                            @foreach($log->data as $key => $value)
                                <div><strong>{{ __("logmessages.data.$key") }}:</strong> {{ $value }}</div>
                            @endforeach
                        @else
                            {{ $log->data }}
                        @endif
                    </td>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </x-table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-card>
@endsection
