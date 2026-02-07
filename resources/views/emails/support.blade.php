@extends('emails.app_mail_new')

@section('mail_content')
    <h1>{{ $subject }}</h1>
    
    <div style="background-color: #f3f4f6; padding: 16px; border-radius: 8px; margin: 24px 0;">
        <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;"><strong>Da:</strong> {{ $contactName }} ({{ $contactEmail }})</p>
        @if(isset($contactSubject) && $contactSubject)
            <p style="margin: 0; color: #6b7280; font-size: 14px;"><strong>Oggetto:</strong> {{ $contactSubject }}</p>
        @endif
    </div>
    
    <div style="margin: 24px 0;">
        <h2 style="font-size: 16px; color: #374151; margin-bottom: 12px;">Messaggio:</h2>
        <div style="white-space: pre-wrap; color: #1f2937; line-height: 1.6;">{{ $body }}</div>
    </div>
    
    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">
        <p style="margin: 0;">Questa è una richiesta di supporto inviata tramite il form di contatto su <strong>{{ config('app.name') }}</strong>.</p>
        <p style="margin: 8px 0 0 0;">Puoi rispondere direttamente a questa email per contattare il mittente.</p>
    </div>
    
    @if(isset($actionUrl) && isset($actionText))
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
        </div>
    @endif
@endsection
