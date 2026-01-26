{{-- resources/views/legal/terms.blade.php --}}
@extends('layouts.public')

@section('title', __('meta.terms_title'))
@section('description', __('meta.terms_description'))

@section('content')
    <main class="w-full max-w-3xl mx-auto bg-[#161615] border border-white/10 rounded-3xl p-6 md:p-10 shadow-xl my-10">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">
            {{ __('legal.terms_title') }}
        </h1>
        <p class="text-sm text-gray-400 mb-8">
            {{ __('legal.last_updated') }} {{ __('legal.last_updated_date') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_purpose_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_purpose_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_eligibility_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_eligibility_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_account_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_account_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_waitlist_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_waitlist_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_acceptable_use_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_acceptable_use_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_ip_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_ip_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_pricing_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_pricing_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_availability_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_availability_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_liability_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_liability_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.terms_law_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.terms_law_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.contact_title') }}
        </h2>
        <p class="mb-1 text-gray-200">
            {{ env('INVOICE_BILLER_NAME') }}
        </p>
        <p class="mb-1 text-gray-200">
            {{ env('INVOICE_BILLER_ADDRESS') }}
        </p>
        <p class="mb-1 text-gray-200">
            {{ env('INVOICE_BILLER_VAT') }}
        </p>
        <p class="mb-1 text-gray-200">
            {{ env('INVOICE_BILLER_EMAIL') }} · {{ env('INVOICE_BILLER_PHONE') }}
        </p>

        <div class="mt-8">
            <a href="{{ url('/') }}" class="text-sm text-blue-400 hover:underline">
                ← {{ __('legal.back_to_home') }}
            </a>
        </div>
    </main>
@endsection
