<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
   
        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */
                @layer theme{...} /* lascio intatto il tuo blob tailwind qui */
            </style>
        @endif

        <style>
            /* HOLO CARD */
            .holo-card {
                position: relative;
                padding: 2px;
                border-radius: 1.25rem;
                background: linear-gradient(
                    120deg,
                    #60a5fa,
                    #a855f7,
                    #f97316,
                    #22c55e,
                    #60a5fa
                );
                background-size: 300% 300%;
                animation: holoShift 10s linear infinite;
                box-shadow: 0 18px 45px rgba(0,0,0,0.45);
                transition: transform 0.25s ease, box-shadow 0.25s ease;
                overflow: hidden;
            }

            .holo-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 25px 55px rgba(0,0,0,0.55);
            }

            .holo-card::before {
                content: "";
                position: absolute;
                inset: -40%;
                background: conic-gradient(
                    from 0deg,
                    rgba(96,165,250,0.0),
                    rgba(96,165,250,0.2),
                    rgba(168,85,247,0.35),
                    rgba(249,115,22,0.25),
                    rgba(45,212,191,0.25),
                    rgba(96,165,250,0.0)
                );
                mix-blend-mode: screen;
                opacity: 0;
                pointer-events: none;
                animation: holoGlow 6s linear infinite;
            }

            .holo-card:hover::before {
                opacity: 0.7;
            }

            @keyframes holoShift {
                0%   { background-position: 0% 50%; }
                50%  { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            @keyframes holoGlow {
                0% {
                    transform: translateX(-20%) rotate(0deg);
                }
                50% {
                    transform: translateX(20%) rotate(180deg);
                }
                100% {
                    transform: translateX(-20%) rotate(360deg);
                }
            }

            /* HOLO OVERLAY SUL BACKGROUND HERO */
            .hero-holo-overlay {
                position: absolute;
                inset: 0;
                pointer-events: none;
                background:
                    radial-gradient(circle at 20% 0%, rgba(96,165,250,0.25), transparent 55%),
                    radial-gradient(circle at 80% 100%, rgba(244,114,182,0.22), transparent 55%),
                    conic-gradient(
                        from 0deg,
                        rgba(96,165,250,0.0),
                        rgba(96,165,250,0.22),
                        rgba(168,85,247,0.30),
                        rgba(249,115,22,0.22),
                        rgba(45,212,191,0.28),
                        rgba(96,165,250,0.0)
                    );
                mix-blend-mode: screen;
                opacity: 0.4;
                animation: heroHoloMove 18s linear infinite;
            }

            @keyframes heroHoloMove {
                0% {
                    transform: translate3d(-5%, -5%, 0) rotate(0deg);
                }
                50% {
                    transform: translate3d(5%, 5%, 0) rotate(180deg);
                }
                100% {
                    transform: translate3d(-5%, -5%, 0) rotate(360deg);
                }
            }
        </style>

    </head>
    <body class="text-white bg-black dark:bg-black dark:text-white flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col" data-theme="dark">

        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 flex items-center">
            <div class="flex-1"></div>

            <!-- Badge 30 giorni prova -->
            <div class="hidden md:flex items-center mr-4">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#161615] border border-yellow-400/60 text-xs font-semibold text-yellow-300 shadow">
                    <span>⭐</span>
                    <span>{{ __('welcome.welcome_message_30_days') }}</span>
                </span>
            </div>

            <form method="POST" action="{{ route('language.change') }}" class="ml-auto">
                @csrf
                <select name="locale" onchange="this.form.submit()" class="px-3 py-1 rounded border border-gray-300 bg-white text-gray-700">
                    <option value="en" @if(app()->getLocale() == 'en') selected @endif>English</option>
                    <option value="it" @if(app()->getLocale() == 'it') selected @endif>Italiano</option>
                    <option value="da" @if(app()->getLocale() == 'da') selected @endif>Dansk</option>
                </select>
            </form>

            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 border border-[#19140035] text-white hover:border-[#1915014a] rounded-sm text-sm leading-normal"
                        >
                            {{ __('welcome.welcome_dashboard') }}
                        </a>
                    @endauth
                </nav>
            @endif
        </header>

        {{-- TOAST WAITLIST --}}
        @if (session('waitlist_success'))
            <div
                id="waitlist-toast"
                class="fixed top-4 right-4 z-50
                    bg-gradient-to-r from-violet-500 via-fuchsia-500 to-amber-400
                    text-slate-900 text-sm md:text-base px-4 py-3
                    rounded-2xl shadow-[0_0_25px_rgba(255,255,255,0.4)]
                    flex items-center gap-3 transition-opacity duration-500"
            >
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/40">
                    ✨
                </span>
                <div class="leading-snug">
                    <div class="font-bold text-xs uppercase tracking-wide">
                        {{ __('welcome.waitlist_success_title') }}
                    </div>
                    <div class="text-[11px] md:text-xs">
                        {{ session('waitlist_success') }}
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    setTimeout(function () {
                        const toast = document.getElementById('waitlist-toast');
                        if (toast) {
                            toast.classList.add('opacity-0', 'pointer-events-none');
                        }
                    }, 4000);
                });
            </script>
        @endif

        <!-- HERO HOLO -->
        <section class="relative w-full min-h-screen flex justify-center pt-20 pb-20 overflow-hidden">

            <!-- Sfondo con carte, meno blur -->
            <img 
                src="{{ asset('images/welcome/hero.jpeg') }}" 
                alt="Hero Background" 
                class="absolute inset-0 w-full h-full object-cover object-center blur-[1px] scale-105"
            >

            <!-- Overlay scuro -->
            <div class="absolute inset-0 bg-black/55"></div>

            <!-- Overlay holo su tutto il background -->
            <div class="hero-holo-overlay"></div>

            <!-- Contenuto centrale -->
            <div class="relative z-10 w-full flex items-center justify-center px-4 py-16 md:py-28">

                <div class="w-full max-w-3xl 
                            holo-card 
                            rounded-3xl 
                            p-[2px]
                            text-center 
                            border border-white/25 
                            backdrop-blur-2xl">

                    <!-- inner content card -->
                    <div class="rounded-3xl bg-black/70 px-8 py-10 md:px-12 md:py-14">

                        <!-- Badge 30 dage prøve -->
                        <span
                           
                                                >

                            ⭐ {{ __('welcome.welcome_message_30_days') }}
                        </span>
                       

                        <!-- Titolo -->
                        <h1 class="text-4xl md:text-5xl font-extrabold mb-6 text-white leading-tight drop-shadow-xl">
                            {!! __('welcome.title') !!}
                        </h1>

                        <!-- Sottotitolo: grassetto + corsivo, chiaro -->
                        <p class="text-lg md:text-xl text-slate-100 mb-10 leading-relaxed font-semibold italic drop-shadow-sm">
                            {{ __('welcome.subtitle') }}
                        </p>

                        @if (config('app.waitlist_enabled'))

                            <!-- WAITLIST CARD -->
                            <section class="w-full flex justify-center mt-4">
                                <div class="w-full max-w-xl bg-[#161615] border border-white/15 rounded-2xl p-6 md:p-8 shadow-xl">

                                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2 text-center">
                                        {{ __('welcome.waitlist_title', ['app' => 'basecard.dk']) }}
                                    </h2>

                                    <p class="text-gray-300 text-sm md:text-base mb-4 text-center">
                                        {{ __('welcome.waitlist_subtitle') }}
                                    </p>

                                    {{-- Badge + contatore animato --}}
                                    @if (isset($waitlistCount) && $waitlistCount >= 0)
                                        <div class="flex flex-col items-center mb-4 space-y-1">

                                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                                                        bg-emerald-500/10 border border-emerald-400/60
                                                        text-emerald-200 text-[11px] md:text-xs">
                                                <span class="text-[10px]">⚡</span>
                                                <span>{{ __('welcome.waitlist_badge') }} </span>
                                            </div>

                                            @if ($waitlistCount === 0)
                                                <p class="text-[11px] md:text-xs text-emerald-200 text-center">
                                                     {{ __('welcome.waitlist_no_one_yet') }})
                                                </p>
                                            @else
                                                @php
                                                    $label = $waitlistCount === 1
                                                        ? __('welcome.waitlist_one') 
                                                        : __('welcome.waitlist_many');
                                                @endphp
                                                <p class="text-[11px] md:text-xs text-emerald-200 text-center">
                                                    <span
                                                        id="waitlist-count"
                                                        data-target="{{ $waitlistCount }}"
                                                        class="font-semibold"
                                                    >
                                                        0
                                                    </span>
                                                    {{ $label }}
                                                </p>
                                            @endif

                                            <div id="launch-countdown"
                                                class="mt-1 text-[11px] md:text-xs text-blue-200 text-center">
                                                <span class="font-semibold">{{ __('welcome.launch_countdown') }}</span>
                                                <span id="launch-date-label">{{ __('welcome.launch_date') }}</span>
                                                <span> · </span>
                                                <span id="launch-timer">{{ __('welcome.launch_count') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if (session('waitlist_success'))
                                        <div class="mb-4 text-xs md:text-sm text-center
                                                    bg-gradient-to-r from-violet-600/40 via-fuchsia-600/40 to-amber-400/30
                                                    border border-violet-300/40 rounded-xl px-3 py-2 text-violet-50 shadow-inner">
                                            💫 Du er på listen! {{ session('waitlist_success') }}
                                        </div>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('waitlist.store') }}"
                                        class="flex flex-col md:flex-row gap-3"
                                        onsubmit="handleWaitlistSubmit(event)"
                                    >
                                        @csrf

                                        <div class="flex-1">
                                            <label for="waitlist_email" class="sr-only">Email</label>
                                            <input
                                                id="waitlist_email"
                                                type="email"
                                                name="email"
                                                required
                                                placeholder="{{ __('welcome.waitlist_placeholder') }}"
                                                value="{{ old('email') }}"
                                                class="w-full px-4 py-3 rounded-xl bg-black/40 border
                                                    @if ($errors->has('email')) border-red-500 @else border-white/20 @endif
                                                    text-white text-sm md:text-base
                                                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                            @if ($errors->has('email'))
                                                <p class="mt-1 text-xs text-red-400">
                                                    {{ $errors->first('email') }}
                                                </p>
                                            @endif
                                        </div>

                                        <button
                                            id="waitlist-submit"
                                            type="submit"
                                            class="px-6 py-3 rounded-xl bg-blue-600 text-white text-sm md:text-base font-semibold shadow-lg hover:bg-blue-700 transition flex items-center justify-center gap-2"
                                        >
                                            <span class="waitlist-label">
                                                {{ __('welcome.join_waitlist') }}
                                            </span>
                                            <span class="waitlist-spinner hidden">
                                                <i class="fa fa-circle-notch fa-spin text-xs"></i>
                                            </span>
                                        </button>
                                    </form>

                                    <p class="mt-3 text-[11px] text-gray-400 text-center">
                                        {!! __('welcome.waitlist_privacy') !!}
                                    </p>
                                </div>
                            </section>

                        @else
                            <div class="flex flex-col sm:flex-row justify-center gap-4">
                                <a href="{{ route('register') }}"
                                   class="px-8 py-4 rounded-xl bg-blue-600 text-white text-lg font-semibold shadow-lg hover:bg-blue-700 transition">
                                    <i class="fa fa-rocket"></i> {{ __('welcome.get_started') }}
                                </a>

                                <a href="{{ route('login') }}"
                                   class="px-8 py-4 rounded-xl bg-white text-blue-700 text-lg font-semibold shadow-lg hover:bg-gray-100 transition">
                                    <i class="fa fa-sign-in-alt"></i> {{ __('welcome.sign_in') }}
                                </a>
                            </div>
                        @endif

                    </div> <!-- fine inner content -->
                </div> <!-- fine holo-card -->
            </div>
        </section>
        <!-- FINE HERO HOLO -->

        <br/>
        <br/>

        <!-- WHO SECTION (LIGHT STRIPE + DARK CARDS) -->
        <section class="w-full flex justify-center mt-16 px-4">
            <div class="w-full max-w-4xl bg-slate-200/60 rounded-3xl shadow-xl px-6 py-12 md:px-10 md:py-14">

                <!-- Title -->
                <h2 class="text-3xl font-bold mb-12 text-center text-slate-900">
                    {{ __('welcome.who_title') }}
                </h2>

                <div class="grid gap-8 md:grid-cols-3">

                    <!-- Card 1 -->
                    <div class="p-6 bg-[#161615] rounded-2xl shadow-lg border border-white/10 
                                flex flex-col items-center text-center gap-4">
                        <img src="{{ asset('images/welcome/01_who.png') }}"
                            alt="{{ __('welcome.who_1_title') }}"
                            class="w-12 h-12"/>

                        <h3 class="font-semibold text-lg text-white">
                            {{ __('welcome.who_1_title') }}
                        </h3>

                        <p class="text-gray-300 text-base leading-relaxed">
                            {{ __('welcome.who_1_desc') }}
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div class="p-6 bg-[#161615] rounded-2xl shadow-lg border border-white/10 
                                flex flex-col items-center text-center gap-4">
                        <img src="{{ asset('images/welcome/02_who.png') }}"
                            alt="{{ __('welcome.who_2_title') }}"
                            class="w-12 h-12"/>

                        <h3 class="font-semibold text-lg text-white">
                            {{ __('welcome.who_2_title') }}
                        </h3>

                        <p class="text-gray-300 text-base leading-relaxed">
                            {{ __('welcome.who_2_desc') }}
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="p-6 bg-[#161615] rounded-2xl shadow-lg border border-white/10 
                                flex flex-col items-center text-center gap-4">
                        <img src="{{ asset('images/welcome/03_who.png') }}"
                            alt="{{ __('welcome.who_3_title') }}"
                            class="w-12 h-12"/>

                        <h3 class="font-semibold text-lg text-white">
                            {{ __('welcome.who_3_title') }}
                        </h3>

                        <p class="text-gray-300 text-base leading-relaxed">
                            {{ __('welcome.who_3_desc') }}
                        </p>
                    </div>

                </div>
            </div>
        </section>


        <!-- HOW IT WORKS SECTION -->
        <section class="w-full flex justify-center mt-20 px-4">
            <div class="w-full max-w-6xl flex flex-col gap-14">

                <!-- TOP ROW: TITLE LEFT + STEPS RIGHT -->
                <div class="grid md:grid-cols-2 gap-10 items-start">

                    <!-- LEFT: TITLE + SUBTITLE -->
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">
                            {{ __('welcome.getting_started') }}
                        </h2>

                        <p class="text-gray-300 text-lg leading-relaxed">
                            {{ __('welcome.getting_started_detail') }}
                        </p>
                    </div>

                    <!-- RIGHT: 3 STEPS SIDE BY SIDE -->
                    <div class="grid grid-cols-3 gap-6 text-gray-200">

                        <!-- Step 1 -->
                        <div class="flex flex-col items-center text-center">
                            <div class="w-10 h-10 mb-3 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold">
                                1
                            </div>
                            <h3 class="font-semibold text-base md:text-lg mb-1">
                                {{ __('welcome.step_1_title') }}
                            </h3>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                {{ __('welcome.step_1_desc') }}
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex flex-col items-center text-center">
                            <div class="w-10 h-10 mb-3 rounded-full bg-purple-600 flex items-center justify-center text-sm font-bold">
                                2
                            </div>
                            <h3 class="font-semibold text-base md:text-lg mb-1">
                                {{ __('welcome.step_2_title') }}
                            </h3>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                {{ __('welcome.step_2_desc') }}
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex flex-col items-center text-center">
                            <div class="w-10 h-10 mb-3 rounded-full bg-emerald-600 flex items-center justify-center text-sm font-bold">
                                3
                            </div>
                            <h3 class="font-semibold text-base md:text-lg mb-1">
                                {{ __('welcome.step_3_title') }}
                            </h3>
                            <p class="text-gray-400 text-sm leading-relaxed">
                                {{ __('welcome.step_3_desc') }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- VIDEO FULL WIDTH UNDER BOTH -->
                <div class="w-full rounded-3xl overflow-hidden shadow-xl border border-white/10 bg-black/40 backdrop-blur">
                    <iframe 
                        class="w-full aspect-video"
                        src="https://www.youtube.com/embed/og9VaT6IyvE"
                        title="basecard.dk Intro"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>

            </div>
        </section>
        <!-- FINE HOW IT WORKS SECTION -->


                <!-- STILL NOT CONVINCED (MID DARK, PIÙ LEGGERO) -->
        <div class="mt-20 w-full flex justify-center px-4">
            <div class="text-center bg-slate-900/80 border border-slate-700 p-10 rounded-3xl w-full max-w-3xl shadow-xl">
                <h2 class="text-3xl font-bold mb-4 text-white">
                    {{ __('welcome.still_not_convinced') }}
                </h2>
                <p class="text-slate-200 text-lg leading-relaxed">
                    {{ __('welcome.pick_plan') }}
                </p>
            </div>
        </div>


        <!-- FAQ -->
        <div class="mt-20 w-full flex justify-center px-4">
            <div class="max-w-3xl w-full">
                <h2 class="text-3xl font-bold mb-8 text-white">
                    {{ __('welcome.faqs_general') }}
                </h2>

                <div class="space-y-3 text-left" id="faq-accordion">
                    <div class="rounded-2xl bg-[#141414] border border-[#3E3E3A]">
                        <button
                            type="button"
                            class="w-full flex justify-between items-center px-5 py-4 text-lg font-semibold text-white focus:outline-none"
                            onclick="toggleFaq(0)"
                        >
                            {{ __('welcome.faq_evalua_q') }}
                            <span id="faq-arrow-0" class="transition-transform ml-4">&#9660;</span>
                        </button>
                        <div id="faq-content-0" class="text-gray-300 px-5 pb-4 hidden text-base leading-relaxed">
                            {{ __('welcome.faq_evalua_a') }}
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[#141414] border border-[#3E3E3A]">
                        <button
                            type="button"
                            class="w-full flex justify-between items-center px-5 py-4 text-lg font-semibold text-white focus:outline-none"
                            onclick="toggleFaq(1)"
                        >
                            {{ __('welcome.faq_start_q') }}
                            <span id="faq-arrow-1" class="transition-transform ml-4">&#9660;</span>
                        </button>
                        <div id="faq-content-1" class="text-gray-300 px-5 pb-4 hidden text-base leading-relaxed">
                            {{ __('welcome.faq_start_a') }}
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[#141414] border border-[#3E3E3A]">
                        <button
                            type="button"
                            class="w-full flex justify-between items-center px-5 py-4 text-lg font-semibold text-white focus:outline-none"
                            onclick="toggleFaq(2)"
                        >
                            {{ __('welcome.faq_upgrade_q') }}
                            <span id="faq-arrow-2" class="transition-transform ml-4">&#9660;</span>
                        </button>
                        <div id="faq-content-2" class="text-gray-300 px-5 pb-4 hidden text-base leading-relaxed">
                            {{ __('welcome.faq_upgrade_a') }}
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[#141414] border border-[#3E3E3A]">
                        <button
                            type="button"
                            class="w-full flex justify-between items-center px-5 py-4 text-lg font-semibold text-white focus:outline-none"
                            onclick="toggleFaq(3)"
                        >
                            {{ __('welcome.faq_trial_q') }}
                            <span id="faq-arrow-3" class="transition-transform ml-4">&#9660;</span>
                        </button>
                        <div id="faq-content-3" class="text-gray-300 px-5 pb-4 hidden text-base leading-relaxed">
                            {{ __('welcome.faq_trial_a') }}
                        </div>
                    </div>
                </div>

                <script>
                    function toggleFaq(idx) {
                        var content = document.getElementById('faq-content-' + idx);
                        var arrow = document.getElementById('faq-arrow-' + idx);
                        var isOpen = !content.classList.contains('hidden');

                        for (let i = 0; i < 4; i++) {
                            document.getElementById('faq-content-' + i).classList.add('hidden');
                            document.getElementById('faq-arrow-' + i).style.transform = '';
                        }

                        if (!isOpen) {
                            content.classList.remove('hidden');
                            arrow.style.transform = 'rotate(180deg)';
                        }
                    }
                </script>
            </div>
        </div>
        <!-- FINE FAQ -->

        <!-- FOOTER -->
            <footer class="w-full mt-20 py-10 px-6 bg-black/40 border-t border-white/10 text-gray-300 text-sm backdrop-blur-xl">

                <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- Colonna 1 - Basios -->
                    <div>
                        <h3 class="font-semibold text-white mb-2">{{ __('welcome.contact_title') }}</h3>
                        <p>{{ env('INVOICE_BILLER_NAME') }}</p>
                        <p>{{ env('INVOICE_BILLER_ADDRESS') }}</p>
                        <p>{{ env('INVOICE_BILLER_VAT') }}</p>
                        <p>{{ env('INVOICE_BILLER_EMAIL') }}</p>
                        <p>{{ env('INVOICE_BILLER_PHONE') }}</p>
                    </div>

                    <!-- Colonna 2 - Informazioni -->
                    <div>
                        <h3 class="font-semibold text-white mb-2">{{ __('welcome.information_title') }}</h3>
                        <p><a href="/terms">{{ __('welcome.terms_conditions') }}</a></p>
                        <p><a href="/privacy">{{ __('welcome.privacy_policy') }}</a></p>
                        <p><a href="mailto:info@basios.dk">{{ __('welcome.support') }}</a></p>
                    </div>

                    <!-- Column 3 - Trademark Disclaimer -->
                    <div>
                        <h3 class="font-semibold text-white mb-2">{{ __('welcome.pokemon_disclaimer_title') }}</h3>
                        <p class="text-xs leading-relaxed text-gray-400">
                            {{ __('welcome.pokemon_disclaimer') }}
                        </p>
                    </div>
                </div>

                <!-- Linea inferiore -->
                <div class="mt-10 text-center text-xs text-gray-500">
                    © {{ date('Y') }} {{ env('INVOICE_BILLER_NAME') }} · {{ __('welcome.all_rights_reserved') }}
                </div>

            </footer>

        <!-- FINE FOOTER -->



        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif

        <script>
            function handleWaitlistSubmit(event) {
                const button = document.getElementById('waitlist-submit');
                if (!button) return;

                button.disabled = true;
                button.classList.add('opacity-80', 'cursor-not-allowed');

                const label   = button.querySelector('.waitlist-label');
                const spinner = button.querySelector('.waitlist-spinner');

                if (label && spinner) {
                    label.textContent = "{{ __('welcome.join_waitlist') }}...";
                    spinner.classList.remove('hidden');
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Contatore animato
                const countEl = document.getElementById('waitlist-count');
                if (countEl) {
                    const target = parseInt(countEl.dataset.target, 10) || 0;

                    if (target <= 0) {
                        countEl.textContent = target;
                    } else {
                        let current   = 0;
                        const duration = 1200;
                        const fps      = 60;
                        const interval = duration / fps;
                        const step     = Math.max(1, Math.round(target / (duration / interval)));

                        const timer = setInterval(function () {
                            current += step;
                            if (current >= target) {
                                current = target;
                                clearInterval(timer);
                            }
                            countEl.textContent = current;
                        }, interval);
                    }
                }

                // Countdown launch
                const timerEl = document.getElementById('launch-timer');
                if (!timerEl) return;

                const target = new Date('2026-01-20T18:00:00');

                function updateCountdown() {
                    const now = new Date();
                    const diff = target - now;

                    if (diff <= 0) {
                        timerEl.textContent = __('welcome.launched');
                        return;
                    }

                    const totalSeconds = Math.floor(diff / 1000);
                    const days   = Math.floor(totalSeconds / 86400);
                    const hours  = Math.floor((totalSeconds % 86400) / 3600);
                    const mins   = Math.floor((totalSeconds % 3600) / 60);

                    let parts = [];
                    if (days > 0) parts.push(days + ' d');
                    parts.push(String(hours).padStart(2, '0') + ' t');
                    parts.push(String(mins).padStart(2, '0') + ' min');

                    timerEl.textContent = parts.join(' · ');
                }

                updateCountdown();
                setInterval(updateCountdown, 60000);
            });
        </script>

    </body>
</html>
