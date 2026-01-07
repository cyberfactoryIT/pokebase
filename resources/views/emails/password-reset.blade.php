@extends('emails.app_mail_new')

@section('mail_content')
    <h1>{{ $subject }}</h1>
    <p>{{ $body }}</p>
    
    @if(isset($actionUrl) && isset($actionText))
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
        </div>
    @endif
    
    <div class="divider"></div>
    
    <div class="warning-box">
        <p style="font-size: 14px;">
            {{ __('messages.If_you_did_not_request_a_password_reset_no_further_action_is_required') }}
        </p>
    </div>
@endsection
