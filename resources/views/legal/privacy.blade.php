{{-- resources/views/legal/privacy.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('legal.privacy_title') }} – {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-black text-white min-h-screen flex flex-col items-center p-6 lg:p-10">
    <main class="w-full max-w-3xl bg-[#161615] border border-white/10 rounded-3xl p-6 md:p-10 shadow-xl">
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
</body>
</html>
