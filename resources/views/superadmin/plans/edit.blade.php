@extends('layouts.app')

@section('content')
    <x-section title="Edit Plan">
        <x-card>
            <form method="POST" action="{{ route('superadmin.plans.update', $plan) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="name" label="Name" value="{{ old('name', $plan->name) }}" required />
                    <x-input name="code" label="Code" value="{{ old('code', $plan->code) }}" required />
                    <x-input name="monthly_price_cents" label="Monthly Price (cents)" type="number" value="{{ old('monthly_price_cents', $plan->monthly_price_cents) }}" required />
                    <x-input name="yearly_price_cents" label="Yearly Price (cents)" type="number" value="{{ old('yearly_price_cents', $plan->yearly_price_cents) }}" required />
                    <x-input name="currency" label="Currency" value="{{ old('currency', $plan->currency) }}" required />
                </div>
                <div class="flex gap-2 mt-4">
                    <x-button type="submit" icon="save">Save</x-button>
                    <x-button as="a" href="{{ route('superadmin.plans.index') }}" type="button" icon="arrow-left">Back</x-button>
                </div>
            </form>
        </x-card>
    </x-section>
@endsection
