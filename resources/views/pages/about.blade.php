@extends('layouts.public')

@section('title', __('pages.about_title') . ' - ' . config('app.name'))

@section('content')
<div class="bg-black min-h-screen py-16">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                {{ __('pages.about_title') }}
            </h1>
            <p class="text-xl text-gray-400">
                {{ __('pages.about_subtitle') }}
            </p>
        </div>

        <!-- Content -->
        <div class="prose prose-invert prose-lg max-w-none">
            <div class="bg-[#161615] border border-white/15 rounded-2xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-white mb-4">{{ __('pages.about_mission_title') }}</h2>
                <p class="text-gray-300 leading-relaxed mb-4">
                    {{ __('pages.about_mission_text') }}
                </p>
            </div>

            <div class="bg-[#161615] border border-white/15 rounded-2xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-white mb-4">{{ __('pages.about_story_title') }}</h2>
                <p class="text-gray-300 leading-relaxed mb-4">
                    {{ __('pages.about_story_text') }}
                </p>
            </div>

            <div class="bg-[#161615] border border-white/15 rounded-2xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-white mb-4">{{ __('pages.about_values_title') }}</h2>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <span class="text-blue-500 text-xl">✓</span>
                        <div>
                            <strong class="text-white">{{ __('pages.about_value1_title') }}</strong>
                            <p class="text-gray-400">{{ __('pages.about_value1_text') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-blue-500 text-xl">✓</span>
                        <div>
                            <strong class="text-white">{{ __('pages.about_value2_title') }}</strong>
                            <p class="text-gray-400">{{ __('pages.about_value2_text') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-blue-500 text-xl">✓</span>
                        <div>
                            <strong class="text-white">{{ __('pages.about_value3_title') }}</strong>
                            <p class="text-gray-400">{{ __('pages.about_value3_text') }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-[#161615] border border-white/15 rounded-2xl p-8">
                <h2 class="text-2xl font-bold text-white mb-4">{{ __('pages.about_team_title') }}</h2>
                <p class="text-gray-300 leading-relaxed">
                    {{ __('pages.about_team_text') }}
                </p>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-16">
            <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-xl text-lg font-semibold transition shadow-lg">
                {{ __('welcome.cta_start_free') }}
            </a>
        </div>
    </div>
</div>
@endsection
