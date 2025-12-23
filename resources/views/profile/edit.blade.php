@extends('layouts.app')

@section('page_title', __('messages.Edit Profile'))

@section('content')
<div class="max-w-xl mx-auto bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
    <h2 class="text-2xl font-bold text-white mb-6">{{ __('messages.Edit_Profile') }}</h2>
    
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <x-input-label for="name" :value="__('messages.name')" class="text-gray-300" />
            <x-input id="name" name="name" type="text" class="mt-1 w-full bg-black/50 border-white/20 text-white" :value="old('name', $user->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div class="mb-4">
            <x-input-label for="email" :value="__('messages.email')" class="text-gray-300" />
            <x-input id="email" name="email" type="email" class="mt-1 w-full bg-black/50 border-white/20 text-white" :value="old('email', $user->email)" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mb-4">
            <x-input-label for="password" :value="__('messages.password_optional')" class="text-gray-300" />
            <x-input id="password" name="password" type="password" class="mt-1 w-full bg-black/50 border-white/20 text-white" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('messages.password_confirmation')" class="text-gray-300" />
            <x-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full bg-black/50 border-white/20 text-white" autocomplete="new-password" />
        </div>
        <div class="flex justify-end">
            <x-button type="submit">{{ __('messages.Save') }}</x-button>
        </div>
    </form>
</div>
@endsection
