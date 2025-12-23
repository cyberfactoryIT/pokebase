@extends('layouts.app')

@section('page_title', __('messages.invoice_data.title', ['number' => $invoice->number]))

@section('content')
<div class="container mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4" vertical-align="top">
                <img src="/images/logo_basecard.jpg" alt="Logo" class="h-12 w-auto">
                <div>
                    <h2 class="text-2xl font-bold">{{ env('INVOICE_BILLER_NAME') }}</h2>
                    <div class="text-sm text-gray-600">{{ env('INVOICE_BILLER_ADDRESS') }}</div>
                    <div class="text-sm text-gray-600">{{ env('INVOICE_BILLER_EMAIL') }}</div>
                    <div class="text-sm text-gray-600">{{ env('INVOICE_BILLER_PHONE') }}</div>
                    <div class="text-sm text-gray-600">{{ env('INVOICE_BILLER_VAT') }}</div>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 rounded-full text-white {{ $invoice->status === __('messages.invoice_data.status.paid') ? 'bg-green-500' : ($invoice->status === __('messages.invoice_data.status.open') ? 'bg-yellow-500' : 'bg-red-500') }}">
                    {{ ucfirst($invoice->status) }}
                </span>
                <div class="mt-2 text-lg font-semibold">{{ __('messages.invoice_data.title', ['number' => $invoice->number]) }}</div>
                <div class="text-sm text-gray-500">{{ __('messages.invoice_data.issued_at') }}: {{ $invoice->issued_at->format('d/m/Y') }}</div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <h3 class="font-bold mb-2">{{ __('messages.invoice_data.billed_to') }}</h3>
                <div class="text-sm text-gray-700">{{ $invoice->org_company }}</div>
                <div class="text-sm text-gray-700">{{ $invoice->org_billing_email }}</div>
                <div class="text-sm text-gray-700">{{ $invoice->org_vat }}</div>
                <div class="text-sm text-gray-700">{{ $invoice->org_address }}</div>
                <div class="text-sm text-gray-700">{{ $invoice->org_city }}, {{ $invoice->org_country }}</div>
            </div>
            <div>
                <h3 class="font-bold mb-2">{{ __('messages.invoice_data.details') }}</h3>
                <div class="text-sm text-gray-700">{{ __('messages.invoice_data.number') }}: {{ $invoice->number }}</div>
                <div class="text-sm text-gray-700">{{ __('messages.invoice_data.issued_at') }}: {{ $invoice->issued_at->format('d/m/Y') }}</div>
                <div class="text-sm text-gray-700">{{ __('messages.invoice_data.due_date') }}: {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</div>
            </div>
        </div>
        <div class="mb-8">
            <h3 class="font-bold mb-2">{{ __('messages.invoice_data.items_title') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('messages.invoice_data.description') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.invoice_data.duration') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.invoice_data.covered_period') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('messages.invoice_data.price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            @php
                                $duration = '-';
                                $periodo = '-';
                                if (preg_match('/\(([^)]+)\) dal (\d{2}\/\d{2}\/\d{4}) al (\d{2}\/\d{2}\/\d{4})/', $item->description, $matches)) {
                                    $duration = $matches[1];
                                    $periodo = $matches[2] . ' - ' . $matches[3];
                                }
                            @endphp
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $item->description }}</td>
                                <td class="px-4 py-2">{{ $duration }}</td>
                                <td class="px-4 py-2">{{ $periodo }}</td>
                                <td class="px-4 py-2 text-right"><x-money :cents="$item->total_cents" :currency="$invoice->currency" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div></div>
            <div>
                <div class="bg-gray-50 rounded-lg p-4 shadow">
                    <h3 class="font-bold mb-2">{{ __('messages.invoice_data.totals') }}</h3>
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between">
                            <span>{{ __('messages.invoice_data.subtotal') }}</span>
                            <span>@include('components.money', ['cents' => $invoice->subtotal_cents, 'currency' => $invoice->currency])</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('messages.invoice_data.discount') }}</span>
                            <span>@include('components.money', ['cents' => $invoice->discount_cents, 'currency' => $invoice->currency])</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('messages.invoice_data.tax') }}</span>
                            <span>@include('components.money', ['cents' => $invoice->tax_cents, 'currency' => $invoice->currency])</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>{{ __('messages.invoice_data.total') }}</span>
                            <span>@include('components.money', ['cents' => $invoice->total_cents, 'currency' => $invoice->currency])</span>
                        </div>
                    </div>
                    @if($invoice->coupon_code)
                        <div class="mt-2">
                            <span class="inline-block px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs">{{ __('messages.invoice_data.coupon') }}: {{ $invoice->coupon_code }}</span>
                        </div>
                    @endif
                    @if($invoice->promotion_snapshot)
                        <div class="mt-2">
                            <span class="inline-block px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs">{{ __('messages.invoice_data.promotion') }}: {{ $invoice->promotion_snapshot['name'] ?? '' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
