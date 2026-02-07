@extends('emails.app_mail_new')

@section('mail_content')
    <h1>{{ $subject }}</h1>
    
    <div class="info-box">
        <p><strong>{{ __('messages.support_email_label_from') }}</strong> {{ $contactName }} ({{ $contactEmail }})</p>
        @if(isset($contactSubject) && $contactSubject)
            <p><strong>{{ __('messages.support_email_label_subject') }}</strong> {{ $contactSubject }}</p>
        @endif
    </div>
    
    <h2>{{ __('messages.support_email_label_message') }}</h2>
    <p style="white-space: pre-wrap;">{{ $body }}</p>
    
    <div class="divider"></div>
    
    <p style="font-size: 14px; opacity: 0.7;">
        {{ __('messages.support_email_footer_line1', ['app_name' => config('app.name')]) }}<br>
        {{ __('messages.support_email_footer_line2') }}
    </p>
    
    @if(isset($actionUrl) && isset($actionText))
        <div style="text-align: center;">
            <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
        </div>
    @endif
@endsection
