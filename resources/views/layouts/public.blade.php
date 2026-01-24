<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name'))</title>
        
        <!-- SEO Meta Tags -->
        <meta name="description" content="@yield('description', __('meta.home_description'))">
        <meta name="keywords" content="samlekort, pokemon kort, trading cards, kortsamling, deck builder, kort værdi">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="@yield('title', config('app.name'))">
        <meta property="og:description" content="@yield('description', __('meta.home_description'))">
        <meta property="og:site_name" content="{{ __('meta.og_site_name') }}">
        <meta property="og:locale" content="{{ __('meta.og_locale') }}">
        @hasSection('og_image')
            <meta property="og:image" content="@yield('og_image')">
        @else
            <meta property="og:image" content="{{ asset('images/og-default.jpg') }}">
        @endif
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="@yield('title', config('app.name'))">
        <meta name="twitter:description" content="@yield('description', __('meta.home_description'))">
        @hasSection('twitter_image')
            <meta name="twitter:image" content="@yield('twitter_image')">
        @else
            <meta name="twitter:image" content="{{ asset('images/og-default.jpg') }}">
        @endif
        
        <!-- Canonical URL -->
        <link rel="canonical" href="@yield('canonical', url()->current())">
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <!-- Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-black text-white" x-data="{ mobileMenuOpen: false }">
        @include('layouts.public-navbar')
        
        <!-- Main Content -->
        <div class="pt-16">
            @yield('content')
        </div>

        <!-- Footer -->
        @include('layouts.footer')
    </body>
</html>
