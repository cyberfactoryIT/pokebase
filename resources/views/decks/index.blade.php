@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ __('decks/index.title') }}</h1>
                <p class="text-gray-400 mt-1">{{ __('decks/index.subtitle') }}</p>
            </div>
            <a href="{{ route('decks.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('decks/index.new_deck') }}
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4 mb-6">
            <p class="text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        @if($decks->isEmpty())
        <!-- Empty State -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-12 text-center">
            <svg class="w-20 h-20 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="text-white text-xl font-semibold mb-2">{{ __('decks/index.empty_title') }}</h3>
            <p class="text-gray-400 mb-6">{{ __('decks/index.empty_body') }}</p>
            <a href="{{ route('decks.create') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                {{ __('decks/index.create_first') }}
            </a>
        </div>
        @else
        <!-- Decks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($decks as $deck)
            <div class="bg-[#161615] border border-white/15 rounded-xl shadow-xl overflow-hidden hover:border-white/30 transition group">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-white font-semibold text-lg mb-1 group-hover:text-blue-400 transition">
                                <a href="{{ route('decks.show', $deck) }}">{{ $deck->name }}</a>
                            </h3>
                            @if($deck->format)
                            <span class="inline-block px-2 py-1 bg-purple-500/20 text-purple-300 text-xs rounded">
                                {{ $deck->format }}
                            </span>
                            @endif
                        </div>
                    </div>

                    @if($deck->description)
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $deck->description }}</p>
                    @endif

                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                        <div class="text-gray-400 text-sm">
                            <span class="font-semibold text-white">{{ $deck->totalCards() }}</span> {{ __('decks/index.cards_count', ['count' => $deck->totalCards()]) }}
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('decks.edit', $deck) }}" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-gray-300 text-sm rounded transition">
                                {{ __('decks/index.edit') }}
                            </a>
                            <a href="{{ route('decks.show', $deck) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                                {{ __('decks/index.view') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
