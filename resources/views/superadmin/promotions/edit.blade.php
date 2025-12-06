@extends('layouts.app')

@section('content')
    <x-section title="{{ __('messages.edit_promotion') }}">
        <x-card>
            <form method="POST" action="{{ route('superadmin.promotions.update', $promotion) }}" class="space-y-4">
                @csrf
                <x-input name="name" label="{{ __('messages.promotion_data.name') }}" value="{{ old('name', $promotion->name) }}" required />
                <x-input name="code" label="{{ __('messages.promotion_data.code') }}" value="{{ old('code', $promotion->code) }}" />
                <x-select name="type" label="{{ __('messages.promotion_data.type') }}" :options="['percent' => __('messages.promotion_data.percent'), 'fixed' => __('messages.promotion_data.fixed')]" :selected="old('type', $promotion->type)" required />
                <x-input name="value" label="{{ __('messages.promotion_data.value') }}" type="number" value="{{ old('value', $promotion->value) }}" required />
                <x-input name="starts_at" label="{{ __('messages.promotion_data.starts_at') }}" type="datetime-local" value="{{ old('starts_at', $promotion->starts_at) }}" />
                <x-input name="ends_at" label="{{ __('messages.promotion_data.ends_at') }}" type="datetime-local" value="{{ old('ends_at', $promotion->ends_at) }}" />
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" {{ old('active', $promotion->active) ? 'checked' : '' }}>
                    {{ __('messages.promotion_data.active') }}
                </label>
                <x-input name="max_redemptions" label="{{ __('messages.promotion_data.max_redemptions') }}" type="number" value="{{ old('max_redemptions', $promotion->max_redemptions) }}" />
                <x-input name="per_org_limit" label="{{ __('messages.promotion_data.per_org_limit') }}" type="number" value="{{ old('per_org_limit', $promotion->per_org_limit) }}" />
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="new_orgs_only" value="1" {{ old('new_orgs_only', $promotion->new_orgs_only) ? 'checked' : '' }}>
                    {{ __('messages.promotion_data.new_orgs_only') }}
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="stackable" value="1" {{ old('stackable', $promotion->stackable) ? 'checked' : '' }}>
                    {{ __('messages.promotion_data.stackable') }}
                </label>
                <x-input name="meta" label="{{ __('messages.promotion_data.meta') }}" value="{{ old('meta', $promotion->meta) }}" />
                <div class="flex gap-2 mt-4">
                    <x-button type="submit" icon="save">{{ __('messages.save') }}</x-button>
                    <x-button as="a" href="{{ route('superadmin.promotions.index') }}" type="button" icon="arrow-left">{{ __('messages.back') }}</x-button>
                </div>
            </form>
        </x-card>
    </x-section>
@endsection
