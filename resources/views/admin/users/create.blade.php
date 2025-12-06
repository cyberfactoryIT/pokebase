@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('messages.create_user') }}
    </h2>
@endsection

@section('content')
    <div class="max-w-xl mx-auto py-8">
        <x-card>
            <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                @csrf

                <div class="relative">
                    <x-input-label for="name">
                        {{ __('messages.name') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
                    </x-input-label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                            <i class="fa fa-user"></i>
                        </span>
                        <x-text-input id="name" name="name" type="text" class="w-full rounded-l-none" required autofocus />
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
                        <x-text-input id="email" name="email" type="email" class="w-full rounded-l-none" required />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="relative">
                    <x-input-label for="password">
                        {{ __('messages.password') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
                    </x-input-label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                            <i class="fa fa-lock"></i>
                        </span>
                        <x-text-input id="password" name="password" type="password" class="w-full rounded-l-none" required />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="relative">
                    <x-input-label for="password_confirmation">
                        {{ __('messages.password_confirmation') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
                    </x-input-label>
                    <div class="flex items-center mt-1">
                        <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                            <i class="fa fa-lock"></i>
                        </span>
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-l-none" required />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="relative">
                    <x-input-label for="role">
                        {{ __('messages.role') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
                    </x-input-label>
                    <x-select 
                        name="role" 
                        :label="__(' ')" 
                        :options="$roles->pluck('name','name')" 
                        :value="old('role')" 
                        placeholder="{{ __('messages.select_role') }}" 
                        class="mt-1 w-full" 
                        required 
                    />
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-2">
                    <x-button as="a" href="{{ route('users.index') }}" variant="neutral" icon="arrow-left">{{ __('messages.cancel') }}</x-button>
                    <x-button type="submit" icon="user-plus">{{ __('messages.save') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
