@extends('layouts.app')
@section('page_title', __('auth.your_recovery_codes'))
@section('content')
<div class="max-w-md mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">{{ __('auth.your_recovery_codes') }}</h1>
    <p class="mb-4">{{ __('auth.store_recovery_codes_message') }}</p>
    <ul class="mb-6 grid grid-cols-1 gap-2">
        @foreach($codes as $code)
            <li class="font-mono bg-gray-100 px-3 py-2 rounded">{{ $code }}</li>
        @endforeach
    </ul>
    <a href="{{ route('2fa.show') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow">{{ __('auth.back_to_2fa_settings') }}</a>
</div>
@endsection
