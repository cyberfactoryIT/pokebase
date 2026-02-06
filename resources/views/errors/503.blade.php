<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - {{ __('errors.maintenance') }} | Basecard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <!-- Logo -->
        <div class="mb-8">
            <img src="{{ asset('images/logo_basecard.svg') }}" alt="Basecard Logo" class="h-16 mx-auto">
        </div>

        <!-- Icon -->
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-r from-yellow-500 to-orange-600 mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
            </div>
        </div>

        <!-- Message -->
        <h2 class="text-3xl font-semibold mb-4">
            {{ __('errors.maintenance_title') }}
        </h2>
        <p class="text-gray-400 text-lg mb-8">
            {{ __('errors.maintenance_description') }}
        </p>

        <!-- Status -->
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500/10 border border-yellow-500/20 rounded-lg mb-8">
            <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
            <span class="text-yellow-400 font-medium">{{ __('errors.maintenance_status') }}</span>
        </div>

        <!-- Action -->
        <div class="flex justify-center mb-8">
            <a href="https://app.basecard.dk/contact" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white font-semibold rounded-lg border border-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                {{ __('errors.contact_support') }}
            </a>
        </div>

        <!-- Additional Info -->
        <p class="text-sm text-gray-500">
            {{ __('errors.maintenance_info') }}
        </p>
    </div>
</body>
</html>
