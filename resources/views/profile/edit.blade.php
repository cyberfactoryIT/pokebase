@extends('layouts.app')

@section('page_title', __('messages.Edit Profile'))

@section('content')
<div class="max-w-xl mx-auto">
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <x-input-label for="name" :value="__('messages.name')" />
            <x-input id="name" name="name" type="text" class="mt-1 w-full" :value="old('name', $user->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div class="mb-4">
            <x-input-label for="email" :value="__('messages.email')" />
            <x-input id="email" name="email" type="email" class="mt-1 w-full" :value="old('email', $user->email)" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mb-4">
            <x-input-label for="password" :value="__('messages.password_optional')" />
            <x-input id="password" name="password" type="password" class="mt-1 w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('messages.password_confirmation')" />
            <x-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full" autocomplete="new-password" />
        </div>
        <div class="flex justify-end">
            <x-button type="submit">{{ __('messages.Save') }}</x-button>
        </div>
    </form>
</div>
@endsection
