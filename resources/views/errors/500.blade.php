<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - {{ __('errors.server_error') }} | Basecard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <!-- Logo -->
        <div class="mb-8">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo_basecard.svg') }}" alt="Basecard Logo" class="h-16 mx-auto">
            </a>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
            <h1 class="text-8xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-orange-600">
                500
            </h1>
        </div>

        <!-- Error Message -->
        <h2 class="text-3xl font-semibold mb-4">
            {{ __('errors.server_error_title') }}
        </h2>
        <p class="text-gray-400 text-lg mb-8">
            {{ __('errors.server_error_description') }}
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                {{ __('errors.go_home') }}
            </a>
            
            <a href="https://app.basecard.dk/contact" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white font-semibold rounded-lg border border-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                {{ __('errors.contact_support') }}
            </a>
        </div>

        <!-- Additional Info -->
        <div class="mt-12 p-4 bg-gray-900 rounded-lg border border-gray-800">
            <p class="text-sm text-gray-400">
                {{ __('errors.server_error_info') }}
            </p>
        </div>
    </div>
</body>
</html>
