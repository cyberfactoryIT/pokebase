@extends('layouts.app')

@section('page_title', __('messages.Edit_User'))

@section('content')

<div class="max-w-xl mx-auto">
    <x-card>
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="relative">
                <x-input-label for="name">
                    {{ __('messages.name') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
                </x-input-label>
                <div class="flex items-center mt-1">
                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                        <i class="fa fa-user"></i>
                    </span>
                    <x-input id="name" name="name" type="text" class="w-full rounded-l-none" :value="old('name', $user->name)" required />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="relative">
                <x-input-label for="email">
                    {{ __('messages.email') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
                </x-input-label>
                <div class="flex items-center mt-1">
                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                        <i class="fa fa-envelope"></i>
                    </span>
                    <x-input id="email" name="email" type="email" class="w-full rounded-l-none" :value="old('email', $user->email)" required />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="relative">
                <x-input-label for="password" :value="__('messages.password_optional')" />
                <div class="flex items-center mt-1">
                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                        <i class="fa fa-lock"></i>
                    </span>
                    <x-input id="password" name="password" type="password" class="w-full rounded-l-none" autocomplete="new-password" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="relative">
                <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />
                <div class="flex items-center mt-1">
                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                        <i class="fa fa-lock"></i>
                    </span>
                    <x-input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-l-none" autocomplete="new-password" />
                </div>
            </div>

            <div class="relative">
                <x-input-label for="role">
                    {{ __('messages.role') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
                </x-input-label>
                <x-select id="role" name="role" class="mt-1 w-full">
                    @foreach([__('messages.admin'),__('messages.manager'),__('messages.team_member'),__('messages.guest'),__('messages.auditor')] as $role)
                        <option value="{{ $role }}" @if($role == (old('role', $roles[0] ?? null))) selected @endif>{{ ucfirst($role) }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-2">
                <x-button as="a" href="{{ route('users.index') }}" variant="neutral" icon="arrow-left">{{ __('messages.Cancel') }}</x-button>
                <x-button type="submit" icon="save">{{ __('messages.Save') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
