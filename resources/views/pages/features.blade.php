@extends('layouts.public')

@section('title', __('meta.features_title'))
@section('description', __('meta.features_description'))

@section('content')
<div class="bg-[#1a1a1a] min-h-screen text-white">
    
    @include('pages.features.hero')
    
    @include('pages.features.collection')
    
    @include('pages.features.market')
    
    @include('pages.features.deckbuilder')
    
    @include('pages.features.analytics')
    
    @include('pages.features.multigame')
    
    @include('pages.features.security')

</div>

<!-- Custom animations -->
<style>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
@endsection
