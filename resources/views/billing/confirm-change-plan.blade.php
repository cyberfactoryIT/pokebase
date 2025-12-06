@extends('layouts.app')

@section('page_title', __('messages.confirm_plan_change'))

@section('content')
<div class="max-w-xl mx-auto">
    <x-section title="{{ __('messages.Plan_Change_Summary') }}">
        <div class="mb-4">
            <strong>{{ __('messages.Plan') }}:</strong> {{ $plan->name }}
        </div>
        <div class="mb-4">
            <strong>{{ __('messages.Amount') }}:</strong> <x-money :cents="$amount" :currency="$plan->currency" />
        </div>
        <div class="mb-4">
            <strong>{{ __('messages.Duration') }}:</strong> {{ $period === 'yearly' ? '1 year' : '1 month' }}
        </div>
        <div class="mb-4">
            <strong>{{ __('messages.Covered_Period') }}:</strong> {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}
        </div>
        <form method="POST" action="{{ route('billing.changePlan') }}">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
            <input type="hidden" name="billing_period" value="{{ $period }}">
            <input type="hidden" name="coupon_code" value="{{ $coupon_code }}">
            @if (!empty($promotionError))
                <div class="mb-4 text-red-600">
                    <strong>{{ $promotionError }}</strong>
                </div>
            @endif
            <div class="flex gap-4 mt-6">
                <x-button type="submit" icon="credit-card">{{ __('messages.Pay') }}</x-button>
                <x-button as="a" href="{{ url()->previous() }}" type="button" icon="arrow-left" class="bg-gray-300 text-gray-700">{{ __('messages.Cancel') }}</x-button>
            </div>
        </form>
    </x-section>
</div>
@endsection
