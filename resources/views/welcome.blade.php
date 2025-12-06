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

      
       <!-- HERO SECTION -->
        <section class="relative w-full min-h-screen flex items-center justify-center overflow-hidden">

            <!-- Background image -->
            <img 
                src="{{ asset('images/welcome/hero.jpeg') }}" 
                alt="Hero Background" 
                class="absolute inset-0 w-full h-full object-cover object-center"
            >

            <!-- Overlay -->

            <div class="absolute inset-0 bg-black/60"></div>

            <!-- Content -->
            <div class="relative w-full h-screen flex items-center justify-center bg-cover bg-center"
            style="background-image: url('/images/hero/bg.jpg');">

            <!-- Dark overlay -->
            <div class="absolute inset-0 bg-black/60"></div>

            <!-- HERO CONTENT CARD -->
            <div class="relative z-10 bg-gray-800/90 text-white rounded-2xl p-10 md:p-16 max-w-3xl w-[90%] text-center shadow-2xl">

                <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight drop-shadow-xl">
                    {!! __('welcome.title') !!}
                </h1>

                <p class="text-xl md:text-2xl text-gray-200 mb-10 leading-relaxed">
                    {{ __('welcome.subtitle') }}
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('register') }}"
                    class="px-8 py-4 rounded-xl bg-blue-600 text-white text-lg font-semibold shadow-lg hover:bg-blue-700 transition">
                        <i class="fa fa-rocket"></i> {{ __('welcome.get_started') }}
                    </a>

                    <a href="{{ route('login') }}"
                    class="px-8 py-4 rounded-xl bg-white/20 text-blue-200 text-lg font-semibold shadow-lg hover:bg-white/30 transition">
                        <i class="fa fa-sign-in-alt"></i> {{ __('welcome.sign_in') }}
                    </a>
                </div>

            </div>
        </div>

        </section>


        <br/>
        <br/>

        <!-- WHO SECTION -->
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
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
