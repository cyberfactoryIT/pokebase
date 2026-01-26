{{-- resources/views/legal/privacy.blade.php --}}
@extends('layouts.public')

@section('title', __('meta.privacy_title'))
@section('description', __('meta.privacy_description'))

@section('content')
    <main class="w-full max-w-3xl mx-auto bg-[#161615] border border-white/10 rounded-3xl p-6 md:p-10 shadow-xl my-10">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">
            {{ __('legal.privacy_title') }}
        </h1>
        <p class="text-sm text-gray-400 mb-8">
            {{ __('legal.last_updated') }} {{ __('legal.last_updated_date') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.privacy_intro_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.privacy_intro_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.controller_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.controller_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.data_we_collect_title') }}
        </h2>
        <p class="mb-2 text-gray-200">
            {{ __('legal.data_we_collect_voluntary') }}
        </p>
        <p class="mb-4 text-gray-200">
            {{ __('legal.data_we_collect_automatic') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.purposes_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.purposes_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.legal_basis_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.legal_basis_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.storage_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.storage_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.retention_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.retention_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.rights_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.rights_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.third_parties_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.third_parties_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.children_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.children_body') }}
        </p>

        <h2 class="mt-6 mb-2 text-xl font-semibold">
            {{ __('legal.security_title') }}
        </h2>
        <p class="mb-4 text-gray-200">
            {{ __('legal.security_body') }}
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
