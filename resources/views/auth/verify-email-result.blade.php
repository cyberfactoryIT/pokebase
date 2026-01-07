@extends('layouts.app')
@section('page_title', __('mail.verification_email.subject'))
@section('content')
<div class="bg-black min-h-screen py-8 flex items-center justify-center">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <a href="/">
                <x-application-logo class="w-32 h-32" />
            </a>
        </div>
        
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <h1 class="text-2xl font-bold mb-6 text-white text-center">{{ __('mail.verification_email.subject') }}</h1>
            
            @if($status === 'success')
                <div class="bg-green-900/30 border border-green-600/50 text-green-400 px-4 py-3 rounded mb-6">
                    {{ __('mail.verification_email.success') }}
                </div>
            @elseif($status === 'already_verified')
                <div class="bg-blue-900/30 border border-blue-600/50 text-blue-400 px-4 py-3 rounded mb-6">
                    {{ __('mail.verification_email.already_verified') }}
                </div>
            @elseif($status === 'expired')
                <div class="bg-red-900/30 border border-red-600/50 text-red-400 px-4 py-3 rounded mb-6">
                    {{ __('mail.verification_email.expired') }}
                </div>
            @else
                <div class="bg-red-900/30 border border-red-600/50 text-red-400 px-4 py-3 rounded mb-6">
                    {{ __('mail.verification_email.invalid') }}
                </div>
            @endif
            
            <div class="text-center">
                <a href="{{ route('dashboard') }}" class="inline-block w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg shadow-lg transition-all duration-200">
                    {{ __('messages.Back_to_home') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
