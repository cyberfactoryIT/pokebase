@extends('layouts.app_no_menu')
@section('page_title', __('mail.verification_email.subject'))
@section('content')
<div class="max-w-md mx-auto py-8 text-center">
    <h1 class="text-2xl font-bold mb-4">{{ __('mail.verification_email.subject') }}</h1>
    <div class="text-red-600 mb-4">{{ __('mail.verification_email.invalid') }}</div>
    <p class="mb-4">{{ __('mail.verification_email.check_mail_verification') }}</p>
    
    <div class="mb-4 text-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow">
                {{ __('mail.verification_email.resend') }}
            </button>
        </form>
    </div>
    <div class="mb-4 text-center">
    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 w-full text-left transition text-center justify-center">
                <i class="fa fa-sign-out-alt text-red-500"></i>
                <span>{{ __('messages.nav.log_out') }}</span>
            </button>
        </form>
    </div>
</div>
@endsection
