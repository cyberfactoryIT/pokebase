@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <!-- Cancel Icon -->
            <div class="mb-6">
                <svg class="w-20 h-20 mx-auto text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>

            <!-- Cancel Message -->
            <h1 class="text-3xl font-bold text-white mb-4">{{ __('checkout.payment_cancelled') }}</h1>
            <p class="text-gray-400 mb-8">{{ __('checkout.payment_cancelled_message') }}</p>

            <!-- Reason (if provided) -->
            @if(request()->has('reason'))
            <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-4 mb-6">
                <p class="text-yellow-200">{{ request()->get('reason') }}</p>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('billing.index') }}" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 inline-block">
                    {{ __('checkout.try_again') }}
                </a>
                
                <a href="{{ route('home') }}" 
                    class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 inline-block">
                    {{ __('checkout.back_to_home') }}
                </a>
            </div>

            <!-- Support Info -->
            <div class="mt-8 p-4 bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-300">
                    {{ __('checkout.need_help') }}
                </p>
                <p class="text-sm text-blue-400 mt-2">
                    <a href="mailto:support@basecard.com" class="hover:underline">support@basecard.com</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
