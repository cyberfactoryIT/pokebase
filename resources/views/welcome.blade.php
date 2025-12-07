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
                overflow: hidden; /* per contenere il bagliore */
            }

            .holo-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 25px 55px rgba(0,0,0,0.55);
            }

            /* Bagliore foil aggiuntivo */
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
                    <span>30 dages gratis prøve</span>
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
                            {{ __('messages.welcome_dashboard') }}
                        </a>
                    @endauth
                </nav>
            @endif
        </header>

      
       <!-- HERO HOLO -->
        <section class="relative w-full min-h-screen flex justify-center pt-20 pb-20 overflow-hidden">

            <!-- Sfondo blur con carte -->
            <img 
                src="{{ asset('images/welcome/hero.jpeg') }}" 
                alt="Hero Background" 
                class="absolute inset-0 w-full h-full object-cover object-center blur-[2px] scale-110"
            >

            <!-- Overlay scuro sopra lo sfondo -->
            <div class="absolute inset-0 bg-black/55"></div>

            <div class="relative z-10 w-full flex items-center justify-center px-4 py-16 md:py-28">

            <div class="w-full max-w-3xl 
                            holo-card 
                            rounded-3xl 
                            p-10 md:p-16 
                            text-center 
                            border border-white/25 
                            backdrop-blur-2xl">
 <br/><br/>
                    <!-- Badge 30 giorni prova -->
                    <div class="flex justify-center mb-6">
                       
                        <span class="holo-badge inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-slate-700 text-sm font-semibold">
                            ⭐ 30 dages gratis prøve · intet kort kræves
                        </span>
                    </div>

                    <!-- Titolo -->
                    <h1 class="text-4xl md:text-5xl font-extrabold mb-6 text-white leading-tight drop-shadow-xl">
                        {!! __('welcome.title') !!}
                    </h1>

                    <!-- Sottotitolo: grassetto + corsivo -->
                    <p class="text-lg md:text-xl text-slate-800 mb-10 leading-relaxed font-semibold italic drop-shadow-sm">
                        {{ __('welcome.subtitle') }}
                    </p>

                    @if (config('app.waitlist_enabled'))    
                                <!-- WAITING LIST -->
                        <section class="w-full flex justify-center px-4 mt-12">
                            <div class="w-full max-w-xl bg-[#161615] border border-white/15 rounded-2xl p-6 md:p-8 shadow-xl">
                                <h2 class="text-2xl md:text-3xl font-bold text-white mb-2 text-center">
                                    {{ __('welcome.waitlist_title', ['app' => 'XXXXXXX']) }}
                                </h2>
                                <p class="text-gray-300 text-sm md:text-base mb-6 text-center">
                                    {{ __('welcome.waitlist_subtitle') }}
                                </p>

                                @if (session('waitlist_success'))
                                    <div class="mb-4 text-sm text-green-300 text-center">
                                        {{ session('waitlist_success') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('waitlist.store') }}" class="flex flex-col md:flex-row gap-3">
                                    @csrf

                                    <div class="flex-1">
                                        <label for="waitlist_email" class="sr-only">Email</label>
                                        <input
                                            id="waitlist_email"
                                            type="email"
                                            name="email"
                                            required
                                            placeholder="din@email.dk"
                                            value="{{ old('email') }}"
                                            class="w-full px-4 py-3 rounded-xl bg-black/40 border border-white/20 text-white text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                        @if ($errors->has('email'))
                                            <p class="mt-1 text-xs text-red-400">
                                                {{ $errors->first('email') }}
                                            </p>
                                        @endif

                                    </div>

                                    <button
                                        type="submit"
                                        class="px-6 py-3 rounded-xl bg-blue-600 text-white text-sm md:text-base font-semibold shadow-lg hover:bg-blue-700 transition"
                                    >
                                        {{ __('welcome.join_waitlist') }}
                                    </button>
                                </form>

                                <p class="mt-3 text-[11px] text-gray-400 text-center">
                                    {{ __('welcome.waitlist_privacy') }}
                                </p>
                            </div>
                        </section>
                    @else   
                        <!-- Bottoni -->
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
<br/><br/>
                </div>
            </div>

        </section>
        <!-- FINE HERO HOLO -->




        <br/>
        <br/>

        <!-- WHO SECTION -->
        <div class="p-6 rounded-2xl bg-[#161615] shadow-xl flex flex-col items-center text-center gap-4
     border border-white/10 shadow-[0_0_20px_rgba(255,255,255,0.08)]">

            <main class="flex max-w-[335px] w-full flex-col lg:max-w-4xl lg:flex-row">
                <div class="mt-12 max-w-2xl w-full mx-auto">
                    
                    <!-- Titolo più grande -->
                    <h2 class="text-3xl font-bold mb-10 text-center text-white">
                        {{ __('welcome.who_title') }}
                    </h2>

                    <div class="grid gap-8 md:grid-cols-3">

                        <!-- Card 1 -->
                        <div class="p-6 bg-[#161615] rounded-2xl shadow flex flex-col items-center text-center gap-4">
                            <img src="{{ asset('images/welcome/01_who.png') }}"
                                alt="{{ __('welcome.who_1_title') }}"
                                class="w-12 h-12"/>

                            <h3 class="font-semibold text-lg text-white mb-1">
                                {{ __('welcome.who_1_title') }}
                            </h3>

                            <p class="text-gray-300 text-base leading-relaxed">
                                {{ __('welcome.who_1_desc') }}
                            </p>
                        </div>

                        <!-- Card 2 -->
                        <div class="p-6 bg-[#161615] rounded-2xl shadow flex flex-col items-center text-center gap-4">
                            <img src="{{ asset('images/welcome/02_who.png') }}"
                                alt="{{ __('welcome.who_2_title') }}"
                                class="w-12 h-12"/>

                            <h3 class="font-semibold text-lg text-white mb-1">
                                {{ __('welcome.who_2_title') }}
                            </h3>

                            <p class="text-gray-300 text-base leading-relaxed">
                                {{ __('welcome.who_2_desc') }}
                            </p>
                        </div>

                        <!-- Card 3 -->
                        <div class="p-6 bg-[#161615] rounded-2xl shadow flex flex-col items-center text-center gap-4">
                            <img src="{{ asset('images/welcome/03_who.png') }}"
                                alt="{{ __('welcome.who_3_title') }}"
                                class="w-12 h-12"/>

                            <h3 class="font-semibold text-lg text-white mb-1">
                                {{ __('welcome.who_3_title') }}
                            </h3>

                            <p class="text-gray-300 text-base leading-relaxed">
                                {{ __('welcome.who_3_desc') }}
                            </p>
                        </div>

                    </div>
                </div>
            </main>
        </div>


        <!-- HOW IT WORKS SECTION -->
        <section class="w-full flex justify-center mt-20 px-4">
            <div class="w-full max-w-4xl grid gap-14 md:grid-cols-2 items-center">
                
                <!-- COLONNA TESTO -->
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 text-white">
                        {{ __('welcome.getting_started') }}
                    </h2>

                    <p class="text-gray-300 mb-8 text-lg leading-relaxed">
                        {{ __('welcome.getting_started_detail') }}
                    </p>

                    <div class="space-y-6 text-gray-200 text-lg">

                        <!-- Step 1 -->
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-sm font-bold">
                                1
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">
                                    {{ __('welcome.step_1_title') }}
                                </h3>
                                <p class="text-gray-300 leading-relaxed">
                                    {{ __('welcome.step_1_desc') }}
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-sm font-bold">
                                2
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">
                                    {{ __('welcome.step_2_title') }}
                                </h3>
                                <p class="text-gray-300 leading-relaxed">
                                    {{ __('welcome.step_2_desc') }}
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-sm font-bold">
                                3
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">
                                    {{ __('welcome.step_3_title') }}
                                </h3>
                                <p class="text-gray-300 leading-relaxed">
                                    {{ __('welcome.step_3_desc') }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- COLONNA MOCKUP -->
                <div class="flex justify-center">
                    <div class="w-full max-w-sm bg-[#161615] rounded-2xl shadow-xl p-8 border border-white/10">

                        <div class="h-64 rounded-xl bg-black/40 flex items-center justify-center text-gray-400 text-base">
                            App mockup / deck view
                        </div>

                        <p class="mt-4 text-sm text-gray-400 text-center">
                            En simpel visning af dine decks og deres værdi.
                        </p>
                    </div>
                </div>

            </div>
        </section>



        

        <!-- STILL NOT CONVINCED -->
        <div class="mt-20 w-full flex justify-center px-4">
            <div class="text-center bg-[#161615] border border-white/10 p-10 rounded-3xl w-full max-w-3xl shadow-xl">
                <h2 class="text-3xl font-bold mb-4 text-white">
                    {{ __('welcome.still_not_convinced') }}
                </h2>
                <p class="text-gray-300 text-lg leading-relaxed">
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

                        // Close all
                        for (let i = 0; i < 4; i++) {
                            document.getElementById('faq-content-' + i).classList.add('hidden');
                            document.getElementById('faq-arrow-' + i).style.transform = '';
                        }

                        // Open selected if was closed
                        if (!isOpen) {
                            content.classList.remove('hidden');
                            arrow.style.transform = 'rotate(180deg)';
                        }
                    }
                </script>
            </div>
        </div>
        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
