@extends('layouts.app')

@section('content')

<div class="bg-black min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
            <h2 class="font-semibold text-2xl text-white mb-6">
                {{ __('messages.Dashboard') }}
            </h2>
            <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4">
                <h3 class="font-semibold text-green-300 mb-2">{{ __('messages.welcome').' '.Auth::user()->name }}!</h3>
                <p class="text-green-200 text-sm">{{ __('messages.you_are_logged_in_correctly') }}</p>
            </div>
            @yield('dashboard_content')
        </div>
    </div>
</div>
@endsection
