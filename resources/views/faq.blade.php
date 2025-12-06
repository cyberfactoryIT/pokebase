@extends('layouts.app')

@section('page_title', __('faq.faq'))

@section('content')
<div class="max-w-3xl mx-auto py-8">
  <div class="mb-8 text-center">
    <h1 class="text-3xl font-bold text-blue-700 mb-2">{{ __('faq.have_questions') }}</h1>
    <p class="text-lg text-gray-700 mb-2">{{ __('faq.we_have_answers') }}</p>
    <p class="text-gray-600">{{ __('faq.intro') }}</p>
  </div>
    @foreach($faqs as $category => $items)
        <h2 class="text-xl font-bold mb-4">{{ $category ?? __('faq.faq') }}</h2>
        @foreach($items as $faq)
            @php
                $q = $faq->question[$lang] ?? reset($faq->question);
                $a = $faq->answer[$lang] ?? reset($faq->answer);
            @endphp
            <details class="mb-4 border rounded-lg p-4 bg-white shadow">
                <summary class="font-semibold cursor-pointer">{{ $q }}</summary>
                <div class="mt-2 prose">{!! \Illuminate\Support\Str::of($a)->markdown()->toHtmlString() !!}</div>
            </details>
        @endforeach
    @endforeach
</div>

@php
  $items = $faqs->flatten()->map(function($f) use ($lang){
      $q = $f->question[$lang] ?? reset($f->question);
      $a = $f->answer[$lang] ?? reset($f->answer);
      return [
        "@type"=>"Question",
        "name"=>$q,
        "acceptedAnswer":[
          "@type"=>"Answer",
          "text"=> \Illuminate\Support\Str::of($a)->markdown()->toHtmlString()
        ]
      ];
  });
@endphp
<script type="application/ld+json">
{!! json_encode([
  "@context"=>"https://schema.org",
  "@type"=>"FAQPage",
  "mainEntity"=>$items
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
</script>
@endsection
