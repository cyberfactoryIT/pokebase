@extends('emails.app_mail_new')

@section('mail_content')
    <h1>{{ $subject }}</h1>
    <p>{{ $body }}</p>
    
    @if(isset($actionUrl) && isset($actionText))
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
        </div>
    @endif
@endsection
