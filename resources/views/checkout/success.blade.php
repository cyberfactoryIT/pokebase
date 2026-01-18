@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <!-- Success Icon -->
            <div class="mb-6">
                <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <!-- Success Message -->
            <h1 class="text-3xl font-bold text-white mb-4">{{ __('checkout.payment_successful') }}</h1>
            <p class="text-gray-400 mb-8">{{ __('checkout.payment_successful_message') }}</p>

            @if(isset($invoice))
            <!-- Invoice Details -->
            <div class="bg-gray-700 rounded-lg p-6 mb-6 text-left">
                <h2 class="text-xl font-semibold text-white mb-4">{{ __('checkout.invoice_details') }}</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between text-gray-300">
                        <span>{{ __('checkout.invoice_number') }}</span>
                        <span class="font-semibold text-white">#{{ $invoice->id }}</span>
                    </div>
                    
                    <div class="flex justify-between text-gray-300">
                        <span>{{ __('checkout.date') }}</span>
                        <span class="font-semibold text-white">{{ $invoice->created_at->format('d/m/Y') }}</span>
                    </div>
                    
                    <div class="flex justify-between text-gray-300">
                        <span>{{ __('checkout.plan') }}</span>
                        <span class="font-semibold text-white">{{ $invoice->items->first()?->description ?? 'N/A' }}</span>
                    </div>
                    
                    @if($invoice->organization && $invoice->organization->renew_date)
                    <div class="flex justify-between text-gray-300">
                        <span>{{ __('checkout.next_renewal') }}</span>
                        <span class="font-semibold text-white">{{ $invoice->organization->renew_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    
                    <div class="border-t border-gray-600 pt-3 mt-3">
                        <div class="flex justify-between text-xl font-bold text-white">
                            <span>{{ __('checkout.total_paid') }}</span>
                            <span>{{ number_format($invoice->total_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('billing.index') }}" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 inline-block">
                    {{ __('checkout.go_to_billing') }}
                </a>
                
                {{-- TODO: Implement invoice download functionality
                @if(isset($invoice))
                <a href="{{ route('invoice.download', $invoice->id) }}" 
                    class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 inline-block">
                    {{ __('checkout.download_invoice') }}
                </a>
                @endif
                --}}
            </div>
            @else
            <!-- No Invoice Found -->
            <div class="mb-6">
                <p class="text-gray-400">{{ __('checkout.no_invoice_found') }}</p>
            </div>
            
            <a href="{{ route('billing.index') }}" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 inline-block">
                {{ __('checkout.go_to_billing') }}
            </a>
            @endif

            <!-- Confirmation Email Notice -->
            <p class="mt-8 text-sm text-gray-500">
                {{ __('checkout.confirmation_email_sent') }}
            </p>
        </div>
    </div>
</div>
@endsection
