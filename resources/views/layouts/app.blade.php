<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->check() ? (auth()->user()->theme ?? 'dark') : 'dark' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    @php
        $shouldAutoStartOnboardingTour = auth()->check()
            && request()->routeIs('dashboard')
            && auth()->user()->onboarding_tour_completed_at === null
            && auth()->user()->onboarding_tour_skipped_at === null;

        $onboardingTourConfig = [
            'buttons' => [
                'next' => __('onboarding.buttons.next'),
                'previous' => __('onboarding.buttons.previous'),
                'done' => __('onboarding.buttons.done'),
                'skip' => __('onboarding.buttons.skip'),
            ],
            'steps' => [
                'dashboard' => [
                    'title' => __('onboarding.steps.dashboard.title'),
                    'description' => __('onboarding.steps.dashboard.description'),
                ],
                'search' => [
                    'title' => __('onboarding.steps.search.title'),
                    'description' => __('onboarding.steps.search.description'),
                ],
                'collection' => [
                    'title' => __('onboarding.steps.collection.title'),
                    'description' => __('onboarding.steps.collection.description'),
                ],
                'deck' => [
                    'title' => __('onboarding.steps.deck.title'),
                    'description' => __('onboarding.steps.deck.description'),
                ],
                'upgrade' => [
                    'title' => __('onboarding.steps.upgrade.title'),
                    'description' => __('onboarding.steps.upgrade.description'),
                ],
            ],
        ];
    @endphp

    <!-- Analytics & App Config -->
    <script>
        window.appConfig = {
            analyticsType: '{{ config("services.analytics.type") }}',
            analyticsId: '{{ config("services.analytics.id") }}',
            analyticsEnabled: {{ config("services.analytics.enabled") ? 'true' : 'false' }},
            shouldAutoStartOnboardingTour: {{ $shouldAutoStartOnboardingTour ? 'true' : 'false' }},
            onboardingTour: <?php echo json_encode($onboardingTourConfig); ?>,
        };
    </script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-black">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')
            <!-- Main Content -->
            <main class="flex-1 p-8 bg-black">
                @isset($header)
                    <header class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl mb-6 p-6">
                        <div class="max-w-7xl mx-auto text-white">
                            {{ $header }}
                        </div>
                    </header>
                @endisset
                <div class="main-section">
                    @yield('content')
                </div>
            </main>
            @include('layouts.footer')
        </div>

        <!-- Cookie Consent Banner -->
        @include('components.cookie-consent')
    </body>
</html>
