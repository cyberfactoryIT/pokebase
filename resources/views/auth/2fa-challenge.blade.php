@extends('layouts.app')
@section('page_title', 'Two-Factor Challenge')
@section('content')
<div class="max-w-md mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Two-Factor Challenge</h1>
    <form method="POST" action="{{ route('2fa.challenge.do') }}" class="mb-4">
        @csrf
        <label class="block mb-2 font-semibold">Authenticator code</label>
        <input type="text" name="code" class="w-full border rounded px-3 py-2 mb-2">
        <label class="block mb-2 font-semibold">Or recovery code</label>
        <input type="text" name="recovery_code" class="w-full border rounded px-3 py-2 mb-2">
        @error('code')<div class="text-red-600 mb-2">{{ $message }}</div>@enderror
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded shadow">Verify</button>
    </form>
</div>
@endsection
