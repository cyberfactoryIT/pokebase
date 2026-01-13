@extends('layouts.public')

@section('title', __('pages.contact_title') . ' - ' . config('app.name'))

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
    <div class="container mx-auto px-6 py-16">
        <h2 class="text-3xl font-bold text-center mb-12">{{ __('pages.contact_categories_title') }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            @php
                // Initialize faqsByCategory if not set
                $faqsByCategory = $faqsByCategory ?? [
                    'getting_started' => collect(),
                    'account_billing' => collect(),
                    'features_tools' => collect(),
                ];
                
                $categories = [
                    [
                        'key' => 'getting_started',
                        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
                        'title' => __('Getting Started'),
                        'fallback' => [
                            __('How to create an account'),
                            __('Setting up your first collection'),
                            __('Import your cards'),
                            __('Understanding pricing data')
                        ]
                    ],
                    [
                        'key' => 'account_billing',
                        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>',
                        'title' => __('Account & Billing'),
                        'fallback' => [
                            __('Manage your account'),
                            __('Subscription plans'),
                            __('Payment methods'),
                            __('Cancel subscription')
                        ]
                    ],
                    [
                        'key' => 'features_tools',
                        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>',
                        'title' => __('Features & Tools'),
                        'fallback' => [
                            __('Collection management'),
                            __('Price tracking'),
                            __('Deck builder'),
                            __('Export options')
                        ]
                    ]
                ];
            @endphp

            @foreach($categories as $category)
            <div class="bg-[#161615] border border-white/15 rounded-lg p-8 hover:border-blue-500/50 transition-colors">
                <div class="text-blue-500 mb-4">
                    {!! $category['icon'] !!}
                </div>
                <h3 class="text-xl font-bold mb-4">{{ $category['title'] }}</h3>
                <ul class="space-y-3">
                    @php
                        $hasFaqs = isset($faqsByCategory[$category['key']]) && 
                                   (is_array($faqsByCategory[$category['key']]) ? count($faqsByCategory[$category['key']]) > 0 : $faqsByCategory[$category['key']]->isNotEmpty());
                    @endphp
                    @if($hasFaqs)
                        @foreach($faqsByCategory[$category['key']] as $faq)
                            @php
                                $question = $faq->question[$lang] ?? reset($faq->question);
                            @endphp
                            <li>
                                <a href="/faq#faq-{{ $faq->id }}" class="text-gray-400 hover:text-blue-400 transition-colors flex items-center">
                                    <span class="mr-2">→</span>
                                    {{ $question }}
                                </a>
                            </li>
                        @endforeach
                    @else
                        @foreach($category['fallback'] as $item)
                        <li>
                            <a href="/faq" class="text-gray-400 hover:text-blue-400 transition-colors flex items-center">
                                <span class="mr-2">→</span>
                                {{ $item }}
                            </a>
                        </li>
                        @endforeach
                    @endif
                </ul>
            </div>
            @endforeach
        </div>

        <!-- View All FAQs Button -->
        <div class="text-center mb-16">
            <a href="/faq" class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                {{ __('pages.contact_all_faqs') }}
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
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
