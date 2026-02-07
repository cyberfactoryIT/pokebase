@extends('emails.app_mail_new')

@section('mail_content')
    <h1>{{ $subject }}</h1>
    
    <div style="background-color: #f3f4f6; padding: 16px; border-radius: 8px; margin: 24px 0;">
        <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;"><strong>{{ __('messages.support_email_label_from') }}</strong> {{ $contactName }} ({{ $contactEmail }})</p>
        @if(isset($contactSubject) && $contactSubject)
            <p style="margin: 0; color: #6b7280; font-size: 14px;"><strong>{{ __('messages.support_email_label_subject') }}</strong> {{ $contactSubject }}</p>
        @endif
    </div>
    
    <div style="margin: 24px 0;">
        <h2 style="font-size: 16px; color: #374151; margin-bottom: 12px;">{{ __('messages.support_email_label_message') }}</h2>
        <div style="white-space: pre-wrap; color: #1f2937; line-height: 1.6;">{{ $body }}</div>
    </div>
    
    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">
        <p style="margin: 0;">{{ __('messages.support_email_footer_line1', ['app_name' => config('app.name')]) }}</p>
        <p style="margin: 8px 0 0 0;">{{ __('messages.support_email_footer_line2') }}</p>
    </div>
    
    @if(isset($actionUrl) && isset($actionText))
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
        </div>
    @endif
@endsection
