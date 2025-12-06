@extends('emails.app_mail')

@section('mail_content')
    <h1>{{ $subject }}</h1>
    <p>{{ $body }}</p>
    @if(isset($actionUrl) && isset($actionText))
        <a href="{{ $actionUrl }}" style="display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:5px;">{{ $actionText }}</a>
    @endif
@endsection
