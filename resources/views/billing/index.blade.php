@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <h1 class="text-2xl font-bold mb-6">{{ __('messages.billing') }}</h1>
            {{-- Current Plan Card --}}
            <div class="mb-8">
                <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                    <h2 class="text-lg font-bold mb-2">{{ __('messages.current_plan') }}</h2>
                    <div>
                        <span class="font-semibold">{{ $org->pricingPlan->name ?? __('messages.none') }}</span>
                        @if(!empty($org->renew_date))
                            <div class="text-sm text-gray-600 mt-1">
                                {{ __('messages.next_renew_date') }}: {{ \Carbon\Carbon::parse($org->renew_date)->format('Y-m-d') }}
                            </div>
                            @if(empty($org->subscription_cancelled) || !$org->subscription_cancelled)
                                <form method="POST" action="{{ route('billing.cancelSubscription') }}" onsubmit="return confirm('{{ __('messages.confirm_cancel_subscription') }}');" class="mt-2">
                                    @csrf
                                    <x-button type="submit" icon="ban" color="danger">{{ __('messages.cancel_subscription') }}</x-button>
                                </form>
                            @else
                                <div class="text-sm text-red-600 mt-1">
                                    {{ __('messages.subscription_cancelled_on') }}: {{ \Carbon\Carbon::parse($org->cancellation_subscription_date)->format('Y-m-d') }}
                                </div>
                                <form method="POST" action="{{ route('billing.reactivateSubscription') }}" onsubmit="return confirm('{{ __('messages.confirm_reactivate_subscription') }}');" class="mt-2">
                                    @csrf
                                    <x-button type="submit" icon="redo" color="success">{{ __('messages.reactivate_subscription') }}</x-button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            {{-- Change Plan Card --}}
            <div class="mb-8">
                <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                    <h2 class="text-lg font-bold mb-2">{{ __('messages.change_plan') }}</h2>
                    <form method="POST" action="{{ route('billing.confirmChangePlan') }}" class="flex gap-4 items-end">
                        @csrf
                        <x-select name="billing_period" id="billing_period" :options="['monthly' => __('messages.plan_monthly'), 'yearly' => __('messages.plan_yearly')]" required placeholder="{{ __('messages.period') }}" value="{{ old('billing_period', 'monthly') }}" />
                        <x-select name="plan_id" required id="plan_id_select">
                            <option value="">{{ __('messages.select_plan') }}</option>
                            @foreach($plans as $plan)
                                @if($plan->id > ($org->pricing_plan_id ?? 0))
                                    <option value="{{ $plan->id }}" data-monthly="{{ number_format($plan->monthly_price_cents / 100, 2) }}" data-yearly="{{ number_format($plan->yearly_price_cents / 100, 2) }}" data-currency="{{ $plan->currency }}">
                                        {{ __('messages.'.$plan->name) }} ({{ number_format($plan->monthly_price_cents / 100, 2) }} {{ $plan->currency }})
                                    </option>
                                @endif
                            @endforeach
                        </x-select>
                        <x-input name="coupon_code" placeholder="{{ __('messages.coupon_code') }}" />
                        <x-button type="submit" icon="exchange-alt">{{ __('messages.change') }}</x-button>
                    </form>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const periodSelect = document.getElementById('billing_period');
                        const planSelect = document.getElementById('plan_id_select');
                        function updatePlanLabels() {
                            const period = periodSelect.value;
                            Array.from(planSelect.options).forEach(opt => {
                                if (!opt.value) return;
                                const price = period === 'yearly' ? opt.getAttribute('data-yearly') : opt.getAttribute('data-monthly');
                                const currency = opt.getAttribute('data-currency');
                                opt.textContent = `${opt.textContent.split(' (')[0]} (${price} ${currency})`;
                            });
                        }
                        periodSelect.addEventListener('change', updatePlanLabels);
                        updatePlanLabels();
                    });
                    </script>
                </div>
            </div>
            {{-- Invoices Card --}}
            <div class="mb-8">
                <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                    <h2 class="text-lg font-bold mb-2">{{ __('messages.invoices') }}</h2>
                    <x-table>
                        <thead>
                            <tr>
                                <th>{{ __('messages.number') }}</th>
                                <th>{{ __('messages.issued') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.total') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->number }}</td>
                                    <td>{{ $invoice->issued_at->format('Y-m-d') }}</td>
                                    <td><x-badge type="{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'open' ? 'warning' : 'danger') }}">{{ ucfirst($invoice->status) }}</x-badge></td>
                                    <td><x-money :cents="$invoice->total_cents" :currency="$invoice->currency" /></td>
                                    <td class="flex gap-2">
                                        <x-button as="a" href="{{ route('billing.invoice.show', $invoice) }}" icon="eye" target="_blank">{{ __('messages.view') }}</x-button>
                                        <x-button as="a" href="{{ route('billing.invoice.receipt', $invoice) }}" icon="file-pdf" target="_blank">{{ __('messages.receipt') }}</x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-table>
                    <div class="mt-4">{{ $invoices->links() }}</div>
                </div>
            </div>
            @if(auth()->user()->hasRole('superadmin'))
                <div class="mb-8">
                    <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                        <h2 class="text-lg font-bold mb-2">{{ __('messages.all_invoices_superadmin') }}</h2>
                        <x-table>
                            <thead>
                                <tr>
                                    <th>{{ __('messages.number') }}</th>
                                    <th>{{ __('messages.organization') }}</th>
                                    <th>{{ __('messages.issued') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.total') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Invoice::orderByDesc('issued_at')->limit(100)->get() as $invoice)
                                    <tr>
                                        <td>{{ $invoice->number }}</td>
                                        <td>{{ $invoice->organization->name ?? $invoice->organization_id }}</td>
                                        <td>{{ $invoice->issued_at->format('Y-m-d') }}</td>
                                        <td><x-badge type="{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'open' ? 'warning' : 'danger') }}">{{ ucfirst($invoice->status) }}</x-badge></td>
                                        <td><x-money :cents="$invoice->total_cents" :currency="$invoice->currency" /></td>
                                        <td class="flex gap-2">
                                            <x-button as="a" href="{{ route('billing.invoice.show', $invoice) }}" icon="eye" target="_blank">View</x-button>
                                            <x-button as="a" href="{{ route('billing.invoice.receipt', $invoice) }}" icon="file-pdf" target="_blank">Receipt</x-button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
