@extends('layouts.app')

@section('page_title', __('messages.Payment_Successful'))

@section('content')
<div class="max-w-xl mx-auto">
    <x-section title="{{ __('messages.Thank_You') }}">
        <div class="mb-4 text-green-700 font-bold text-xl">
            {{ __('messages.Payment_Successful') }}
        </div>
        <div class="mb-4">
            <strong>{{ __('messages.Plan') }}:</strong> {{ $plan->name }}<br>
            <strong>{{ __('messages.Amount') }}:</strong> <x-money :cents="$amount" :currency="$plan->currency" /><br>
            <strong>{{ __('messages.Duration') }}:</strong> {{ $period === 'yearly' ? __('messages.one_year') : __('messages.one_month') }}<br>
            <strong>{{ __('messages.Covered_Period') }}:</strong> {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}
        </div>
        <x-button as="a" href="{{ route('billing.index') }}" icon="file-invoice">
            {{ __('messages.View_Invoices') }}
        </x-button>
    </x-section>
</div>
@endsection
