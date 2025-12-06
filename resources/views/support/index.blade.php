@extends('layouts.app')

@section('page_title', __('faq.support'))

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-3xl font-bold mb-2 text-blue-700">{{ __('faq.have_questions') }}</h1>
    <p class="text-lg text-gray-700 mb-2">{{ __('faq.we_have_answers') }}</p>
    <p class="mb-6 text-gray-700">{{ __('faq.support_intro') }}</p>
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <button id="contact-toggle" type="button" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded shadow">{{ __('faq.contact_us') }}</button>
    <form id="contact-form" method="POST" action="{{ route('support.contact') }}" class="bg-white rounded shadow p-6 mb-8" style="display:none;">
         @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-1">{{ __('faq.name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required maxlength="64">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">{{ __('faq.email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">{{ __('faq.message') }}</label>
            <textarea name="message" rows="5" class="w-full border rounded px-3 py-2" required maxlength="2000">{{ old('message') }}</textarea>
        </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow">{{ __('faq.send') }}</button>
        <button type="button" id="contact-cancel" class="px-4 py-2 bg-gray-300 text-gray-800 rounded shadow">{{ __('faq.cancel') }}</button>
    </div>
    </form>

    

<script>
document.getElementById('contact-toggle').addEventListener('click', function() {
    var form = document.getElementById('contact-form');
    form.style.display = 'block';
    this.style.display = 'none';
});
document.getElementById('contact-cancel').addEventListener('click', function() {
    var form = document.getElementById('contact-form');
    form.style.display = 'none';
    document.getElementById('contact-toggle').style.display = 'inline-block';
});
</script>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
    
        @foreach($faqs as $category => $items)
            <div>
                <h2 class="text-xl font-bold mb-4 text-blue-700">{{ $category }}</h2>
                <div class="space-y-2">
                    @foreach($items as $faq)
                        @php
                            $lang = request('lang', app()->getLocale());
                            $q = $faq->question[$lang] ?? reset($faq->question);
                            $a = $faq->answer[$lang] ?? reset($faq->answer);
                        @endphp
                        <details class="bg-blue-50 rounded border p-3">
                            <summary class="font-semibold cursor-pointer">{{ $q }}</summary>
                            <div class="mt-2 text-gray-700">{!! \Illuminate\Support\Str::of($a)->markdown()->toHtmlString() !!}</div>
                        </details>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

<script src="//unpkg.com/alpinejs" defer></script>

</div>
@endsection
