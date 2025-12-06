@extends('layouts.app')

@section('page_title', isset($faq) ? __('faq.edit_faq') : __('faq.add_faq'))

@section('content')
<div class="max-w-xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-8">
        <form method="POST" action="{{ isset($faq) ? route('faqs.update', $faq) : route('faqs.store') }}">
            @csrf
            @if(isset($faq))
                @method('PUT')
            @endif
            <div class="mb-4">
                <label class="block font-semibold mb-1">{{ __('faq.category') }}</label>
                <input type="text" name="category" value="{{ old('category', $faq->category ?? '') }}" class="w-full border rounded px-3 py-2" maxlength="64">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">{{ __('faq.question') }}</label>
                <div id="locales">
                    @php
                        $locales = old('question', $faq->question ?? ['en'=>'','it'=>'']);
                    @endphp
                    @foreach($locales as $locale => $question)
                        <div class="flex gap-2 mb-2">
                            <input type="text" name="question[{{ $locale }}]" value="{{ $question }}" placeholder="{{ strtoupper($locale) }} question" class="w-1/3 border rounded px-2 py-1">
                            <textarea name="answer[{{ $locale }}]" placeholder="{{ strtoupper($locale) }} answer" class="w-2/3 border rounded px-2 py-1">{{ old('answer.'.$locale, $faq->answer[$locale] ?? '') }}</textarea>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mb-4 flex gap-4 items-center">
                <label class="font-semibold">{{ __('faq.is_published') }}</label>
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $faq->is_published ?? false) ? 'checked' : '' }}>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">{{ __('faq.published_at') }}</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($faq) && $faq->published_at ? $faq->published_at->format('Y-m-d\TH:i') : '') }}" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">{{ __('faq.sort_order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" min="0" class="w-full border rounded px-3 py-2">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow">{{ isset($faq) ? __('faq.update') : __('faq.save') }}</button>
        </form>
    </div>
</div>
@endsection
