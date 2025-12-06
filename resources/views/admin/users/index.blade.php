@extends('layouts.app')

@section('page_title', __('messages.Users'))

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ __('Users') }}</h2>
                <x-button as="a" href="{{ route('users.create') }}" icon="user-plus">
                    {{ __('messages.create_user') }}
                </x-button>
            </div>
            <div class="overflow-x-auto">
                <x-table>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('messages.name') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.email') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.roles') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff&size=32" alt="Avatar" class="h-8 w-8 rounded-full">
                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                            </td>
                            <td class="px-4 py-2 text-gray-700">{{ $user->email }}</td>
                            <td class="px-4 py-2">
                                @foreach($user->roles as $role)
                                    <x-badge variant="primary">{{ ucfirst($role->name) }}</x-badge>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-center flex gap-2 justify-center">
                                <x-button as="a" href="{{ route('users.edit', $user) }}" icon="pen" variant="neutral" title="Edit">
                            
                                </x-button>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" icon="trash" title="Delete">
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </div>
    </x-card>
    </div>
</div>
@endsection
