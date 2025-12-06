@extends('layouts.app')
@section('page_title', __('mail.verification_email.subject'))
@section('content')
<div class="max-w-md mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">{{ __('mail.verification_email.subject') }}</h1>
    @if($status === 'success')
        <div class="text-green-600 mb-4">{{ __('mail.verification_email.success') }}</div>
    @elseif($status === 'already_verified')
        <div class="text-blue-600 mb-4">{{ __('mail.verification_email.already_verified') }}</div>
    @elseif($status === 'expired')
        <div class="text-red-600 mb-4">{{ __('mail.verification_email.expired') }}</div>
    @else
        <div class="text-red-600 mb-4">{{ __('mail.verification_email.invalid') }}</div>
    @endif
    <a href="/" class="px-4 py-2 bg-blue-600 text-white rounded shadow">{{ __('messages.Back to home') }}</a>
</div>
@endsection
