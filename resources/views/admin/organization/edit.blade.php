@extends('layouts.app')

@section('page_title', __('messages.Edit_Organization'))

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
    <x-card>
            @if(Auth::user()->hasRole('admin'))
            <div class="mb-4 flex justify-end">
                <x-button as="a" href="{{ route('billing.index') }}" icon="credit-card">
                    {{ __('messages.Billing_Plans') }}
                </x-button>
            </div>
            @endif
            @if(session('status'))
                <div class="mb-4">
                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded">{{ session('status') }}</span>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.organization.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')
                <div class="relative">
                    <x-input-label for="name">{{ __('messages.organization_name') }}</x-input-label>
                    <x-input id="name" name="name" type="text" class="w-full bg-gray-100" :value="$organization->name" readonly />
                </div>
                <div class="relative">
                    <x-input-label for="code">{{ __('Code') }}</x-input-label>
                    <x-input id="code" name="code" type="text" class="w-full bg-gray-100" :value="$organization->code" readonly />
                </div>
                <div class="mb-8">
                    <h2 class="text-lg font-semibold mb-2">{{ __('messages.Billing_Details') }}</h2>
                    <div class="space-y-6">
                        <div class="relative">
                            <x-input-label for="company">{{ __('messages.organization_name') }}</x-input-label>
                            <x-input id="company" name="company" type="text" class="w-full" :value="old('company', $organization->company)" />
                            <x-input-error :messages="$errors->get('company')" class="mt-2" />
                        </div>
                        <div class="relative">
                            <x-input-label for="billing_email">{{ __('messages.Billing_Email') }}</x-input-label>
                            <x-input id="billing_email" name="billing_email" type="email" class="w-full" :value="old('billing_email', $organization->billing_email)" />
                            <x-input-error :messages="$errors->get('billing_email')" class="mt-2" />
                        </div>
                        <div class="relative">
                            <x-input-label for="vat_number">{{ __('messages.VAT_Number') }}</x-input-label>
                            <x-input id="vat_number" name="vat_number" type="text" class="w-full" :value="old('vat_number', $organization->vat_number)" />
                            <x-input-error :messages="$errors->get('vat_number')" class="mt-2" />
                        </div>
                        <div class="relative">
                            <x-input-label for="address_line1">{{ __('messages.Address_Line_1') }}</x-input-label>
                            <x-input id="address_line1" name="address_line1" type="text" class="w-full" :value="old('address_line1', $organization->address_line1)" />
                            <x-input-error :messages="$errors->get('address_line1')" class="mt-2" />
                        </div>
                        <div class="relative">
                            <x-input-label for="address_line2">{{ __('messages.Address_Line_2') }}</x-input-label>
                            <x-input id="address_line2" name="address_line2" type="text" class="w-full" :value="old('address_line2', $organization->address_line2)" />
                            <x-input-error :messages="$errors->get('address_line2')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="relative">
                                <x-input-label for="postcode">{{ __('messages.Postcode') }}</x-input-label>
                                <x-input id="postcode" name="postcode" type="text" class="w-full" :value="old('postcode', $organization->postcode)" />
                                <x-input-error :messages="$errors->get('postcode')" class="mt-2" />
                            </div>
                            <div class="relative">
                                <x-input-label for="city">{{ __('messages.City') }}</x-input-label>
                                <x-input id="city" name="city" type="text" class="w-full" :value="old('city', $organization->city)" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                            <div class="relative">
                                <x-input-label for="country">{{ __('messages.Country') }}</x-input-label>
                                <select id="country" name="country" class="w-full">
                                    @foreach($countries as $country)
                                        <option value="{{ $country->name_en }}" @if(old('country', $organization->country) == $country->name_en) selected @endif>{{ $country->name_en }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('country')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <x-button as="a" href="{{ route('dashboard') }}" variant="neutral" icon="arrow-left">{{ __('Cancel') }}</x-button>
                    <x-button type="submit" icon="save">{{ __('Save') }}</x-button>
                </div>
            </form>
    </x-card>
    </div>
</div>
@endsection
