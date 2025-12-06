@extends('layouts.app')

@section('page_title', __('faq.faq'))

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ __('faq.faq') }}</h2>
                @if(auth()->user() && auth()->user()->hasRole('superadmin'))
                    <x-button as="a" href="{{ route('faq.create') }}" icon="plus" variant="primary">
                        {{ __('messages.add_faq') }}
                    </x-button>
                @endif
            </div>
            <form method="GET" class="mb-6 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_faq') }}" class="px-3 py-2 rounded-lg border border-gray-300 shadow-sm bg-white text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition w-full" />
                <x-button type="submit" icon="search" variant="primary">{{ __('messages.search') }}</x-button>
            </form>
            <x-accordion :items="$faqs" />
        </div>
    </div>
</div>
@endsection
