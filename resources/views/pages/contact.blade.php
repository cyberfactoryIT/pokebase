@extends('layouts.public')

@section('title', __('meta.contact_title'))
@section('description', __('meta.contact_description'))

@section('content')
@php
    // Set lang if not provided
    $lang = $lang ?? app()->getLocale();
@endphp

<div class="min-h-screen bg-black text-white">
    <!-- Hero Section with Search -->
    <div class="bg-gradient-to-br from-blue-600 to-purple-600 py-20">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl font-bold mb-4">{{ __('pages.contact_title') }}</h1>
            <p class="text-xl mb-8 text-blue-100">{{ __('pages.contact_subtitle') }}</p>
            
            <!-- Search Bar -->
            <div class="max-w-2xl mx-auto">
                <div class="relative">
                    <input 
                        type="text" 
                        id="faq-search"
                        placeholder="{{ __('pages.contact_search_placeholder') }}"
                        class="w-full px-6 py-4 pr-12 rounded-lg text-gray-900 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Categories Section -->
    <div class="container mx-auto px-6 py-16" x-data="{ selectedFaq: null }">
        <h2 class="text-3xl font-bold text-center mb-12">{{ __('pages.contact_categories_title') }}</h2>
        
        @if(isset($faqs) && $faqs->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            @foreach($faqs as $category => $items)
            <div class="bg-[#161615] border border-white/15 rounded-lg p-8 hover:border-blue-500/50 transition-colors">
                <div class="text-blue-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-4">{{ $category }}</h3>
                <ul class="space-y-3">
                    @foreach($items->take(4) as $faq)
                        @php
                            $question = $faq->question[$lang] ?? reset($faq->question);
                            $answer = $faq->answer[$lang] ?? reset($faq->answer);
                        @endphp
                        <li>
                            <button 
                                @click="selectedFaq = selectedFaq === {{ $faq->id }} ? null : {{ $faq->id }}; $nextTick(() => { if(selectedFaq === {{ $faq->id }}) { document.getElementById('faq-display').scrollIntoView({ behavior: 'smooth', block: 'center' }); } })"
                                class="text-gray-400 hover:text-blue-400 transition-colors flex items-start text-left w-full"
                                :class="{ 'text-blue-400': selectedFaq === {{ $faq->id }} }"
                            >
                                <span class="mr-2 flex-shrink-0">→</span>
                                <span>{{ $question }}</span>
                            </button>
                            <template x-if="false" x-data="{ q: @js($question), a: @js($answer), id: {{ $faq->id }} }"></template>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
        
        <!-- FAQ Display Area -->
        <div id="faq-display" x-show="selectedFaq !== null" x-cloak class="mb-16">
            @foreach($faqs as $category => $items)
                @foreach($items as $faq)
                    @php
                        $question = $faq->question[$lang] ?? reset($faq->question);
                        $answer = $faq->answer[$lang] ?? reset($faq->answer);
                    @endphp
                    <div x-show="selectedFaq === {{ $faq->id }}" class="bg-[#161615] border border-blue-500/50 rounded-lg p-8">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-2xl font-bold text-blue-400">{{ $question }}</h3>
                            <button @click="selectedFaq = null" class="text-gray-400 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="prose prose-invert max-w-none text-gray-300">
                            {!! \Illuminate\Support\Str::of($answer)->markdown()->toHtmlString() !!}
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
        
        @else
        <p class="text-center text-gray-400 mb-16">{{ __('pages.contact_no_faqs') }}</p>
        @endif
    </div>

    <!-- Contact Form Section -->
    <div class="bg-[#161615] border-t border-white/15 py-16">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4">{{ __('pages.contact_or_contact') }}</h2>
            </div>

            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div>
                    <h3 class="text-2xl font-bold mb-6">{{ __('pages.contact_form_title') }}</h3>
                    <form action="{{ route('support.contact') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium mb-2">{{ __('pages.contact_form_name') }}</label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium mb-2">{{ __('pages.contact_form_email') }}</label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium mb-2">{{ __('pages.contact_form_subject') }}</label>
                            <input type="text" id="subject" name="subject" required class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium mb-2">{{ __('pages.contact_form_message') }}</label>
                            <textarea id="message" name="message" rows="6" required class="w-full px-4 py-3 bg-black border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 rounded-lg font-semibold transition-all">
                            {{ __('pages.contact_form_send') }}
                        </button>
                    </form>

                    @if(session('contact_success'))
                        <div class="mt-4 p-4 bg-green-600/20 border border-green-500/50 rounded-lg text-green-400">
                            {{ session('contact_success') }}
                        </div>
                    @endif
                </div>

                <!-- Contact Information -->
                <div class="space-y-8">
                    <div>
                        <h3 class="text-2xl font-bold mb-6">{{ __('pages.contact_email_title') }}</h3>
                        <div class="bg-black border border-white/20 rounded-lg p-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-500 mt-1 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-gray-400 mb-2">{{ __('pages.contact_email_title') }}</p>
                                    <a href="mailto:{{ config('company-info.email') ?: 'info@basios.dk' }}" class="text-blue-400 hover:text-blue-300">{{ config('company-info.email') ?: 'info@basios.dk' }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-2xl font-bold mb-6">{{ __('pages.contact_company_title') }}</h3>
                        <div class="bg-black border border-white/20 rounded-lg p-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-500 mt-1 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <div>
                                    <p class="text-gray-400 mb-2">{{ config('company-info.name') ?: 'Basios Aps' }}</p>
                                    <p class="text-gray-400">{{ __('pages.contact_company_vat') }}: {{ config('company-info.vat') ?: 'DK 46023021' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-2xl font-bold mb-6">{{ __('pages.contact_location_title') }}</h3>
                        <div class="bg-black border border-white/20 rounded-lg p-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-500 mt-1 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-gray-400">{{ config('company-info.address') ?: 'Støberigården 11, 2. 1, 7500 Holstebro' }}</p>
                                    <p class="text-gray-400">{{ config('company-info.city') ?: 'Holstebro, Danmark' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faq-search');
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                if (query) {
                    window.location.href = '/faq?search=' + encodeURIComponent(query);
                }
            }
        });
    }
});
</script>
@endsection
