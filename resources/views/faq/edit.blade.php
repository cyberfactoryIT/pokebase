@extends('layouts.app')

@section('page_title', __('faq.edit_faq'))

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-2xl font-bold mb-6">{{ __('faq.edit_faq') }}</h2>
            <form method="POST" action="{{ route('faq.update', $faq) }}">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block font-semibold mb-1">{{ __('faq.question') }}</label>
                    <input type="text" name="question" class="w-full border rounded px-3 py-2" value="{{ $faq->question }}" required>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold mb-1">{{ __('faq.answer') }}</label>
                    <textarea name="answer" class="w-full border rounded px-3 py-2" rows="5" required>{{ $faq->answer }}</textarea>
                </div>
                <x-button type="submit" icon="save" variant="primary">{{ __('faq.update') }}</x-button>
            </form>
        </div>
    </div>
</div>
@endsection
