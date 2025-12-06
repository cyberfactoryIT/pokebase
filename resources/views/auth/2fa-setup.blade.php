@extends('layouts.app')
@section('page_title', 'Two-Factor Authentication')
@section('content')
<div class="max-w-md mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Two-Factor Authentication</h1>
    @if(!$enabled)
        <div class="mb-6">
            <p class="mb-2">Scan this QR code with Google Authenticator, 1Password, or Authy:</p>
            <img src="{{ $qr }}" alt="QR Code" class="mb-2 mx-auto">
            <p class="mb-2">Or enter this secret manually: <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $secret }}</span></p>
        </div>
        <form method="POST" action="{{ route('2fa.confirm') }}" class="mb-4">
            @csrf
            <label class="block mb-2 font-semibold">Enter code from your app:</label>
            <input type="text" name="code" class="w-full border rounded px-3 py-2 mb-2" required>
            @error('code')<div class="text-red-600 mb-2">{{ $message }}</div>@enderror
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow">Confirm &amp; Enable</button>
        </form>
    @else
        <div class="mb-4 text-green-700">Two-factor authentication is enabled.</div>
        <form method="POST" action="{{ route('2fa.recovery') }}" class="mb-2">
            @csrf
            <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded shadow">Regenerate Recovery Codes</button>
        </form>
        <form method="POST" action="{{ route('2fa.disable') }}" class="mb-2">
            @csrf
            <label class="block mb-2 font-semibold">Confirm password to disable:</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-2" required>
            @error('password')<div class="text-red-600 mb-2">{{ $message }}</div>@enderror
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded shadow">Disable 2FA</button>
        </form>
    @endif
</div>
@endsection
