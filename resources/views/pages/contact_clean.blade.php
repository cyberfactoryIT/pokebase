@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-16">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                {{ __('pages.contact_title') }}
            </h1>
            <p class="text-xl text-gray-400">
                {{ __('pages.contact_subtitle') }}
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Contact Info -->
            <div class="space-y-6">
                <div class="bg-[#161615] border border-white/15 rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-600/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">{{ __('pages.contact_email_title') }}</h3>
                            <a href="mailto:{{ config('mail.from.address') }}" class="text-blue-400 hover:text-blue-300">
                                {{ config('mail.from.address') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-[#161615] border border-white/15 rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-purple-600/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">{{ __('pages.contact_company_title') }}</h3>
                            <p class="text-gray-400">{{ config('company-info.name') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('pages.contact_company_vat') }}: {{ config('company-info.vat') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#161615] border border-white/15 rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-600/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-1">{{ __('pages.contact_location_title') }}</h3>
                            <p class="text-gray-400">{{ config('company-info.address') }}</p>
                            <p class="text-gray-400">{{ config('company-info.city') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-[#161615] border border-white/15 rounded-2xl p-8">
                <h2 class="text-2xl font-bold text-white mb-6">{{ __('pages.contact_form_title') }}</h2>
                
                <form action="{{ route('contact.send') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ __('pages.contact_form_name') }}
                        </label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ __('pages.contact_form_email') }}
                        </label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ __('pages.contact_form_subject') }}
                        </label>
                        <input type="text" id="subject" name="subject" required
                            class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ __('pages.contact_form_message') }}
                        </label>
                        <textarea id="message" name="message" rows="5" required
                            class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-500 transition resize-none"></textarea>
                    </div>

                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg font-semibold transition shadow-lg">
                        {{ __('pages.contact_form_send') }}
                    </button>
                </form>

                @if(session('contact_success'))
                    <div class="mt-4 p-4 bg-green-600/20 border border-green-500/50 rounded-lg text-green-400">
                        {{ session('contact_success') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
