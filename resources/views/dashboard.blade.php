@extends('layouts.app')

@section('content')

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <x-card>
            <h2 class="font-semibold text-2xl text-gray-800 mb-6">
                {{ __('messages.Dashboard') }}
            </h2>
            <x-alert type="success" heading="{{ __('messages.welcome').' '.Auth::user()->name }}!">
                {{ __('messages.you_are_logged_in_correctly') }}
            </x-alert>
            @yield('dashboard_content')
        </x-card>
    </div>
</div>
@endsection
