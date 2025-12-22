@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8 flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('messages.email')" class="text-gray-300" />
                    <x-text-input id="email" class="block mt-1 w-full bg-black/50 border-white/20 text-white" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('messages.password')" class="text-gray-300" />

                    <x-text-input id="password" class="block mt-1 w-full bg-black/50 border-white/20 text-white"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-white/20 bg-black/50 text-blue-500 shadow-sm focus:ring-blue-500" name="remember">
                        <span class="ms-2 text-sm text-gray-300">{{ __('messages.remember_me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('password.request') }}">
                            {{ __('messages.forgot_password') }}
                        </a>
                    @endif

                    <x-primary-button class="ms-3">
                        {{ __('messages.log_in') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
