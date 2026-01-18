@extends('layouts.app')

@section('page_title', __('messages.invoice_data.title', ['number' => $invoice->number]))

@push('styles')
<style>
    @media print {
        /* Hide navigation, footer, and other UI elements */
        nav, header, footer, .print\\:hidden {
            display: none !important;
        }
        
        /* Remove page background and padding */
        body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Full width for invoice */
        .container {
            max-width: 100% !important;
            padding: 0 !important;
        }
        
        /* Remove shadows and rounded corners for clean print */
        .shadow-lg, .rounded-lg {
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        
        /* Ensure text is black */
        * {
            color: black !important;
        }
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <!-- Print Button -->
        <div class="max-w-4xl mx-auto mb-4">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold shadow-lg print:hidden">
                <i class="fas fa-print"></i>
                <span>{{ __('billing.invoice.print') }}</span>
            </button>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8 max-w-4xl mx-auto">
            <div class="flex justify-between items-start mb-8 pb-6 border-b-2 border-gray-200">
                <div class="flex items-center gap-4">
                    <img src="/images/logo_basecard.jpg" alt="Logo" class="h-16 w-auto">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ config('invoice.biller_name') }}</h2>
                        <div class="text-sm text-gray-700 mt-1">{{ config('invoice.biller_address') }}</div>
                        <div class="text-sm text-gray-700">{{ config('invoice.biller_email') }}</div>
                        <div class="text-sm text-gray-700">{{ config('invoice.biller_phone') }}</div>
                        <div class="text-sm text-gray-700 font-medium">{{ __('billing.invoice.vat') }}: {{ config('invoice.biller_vat') }}</div>
                    </div>
                </div>
                <div class="text-right">
                    @php
                        $statusLabel = __('billing.invoice.status_' . $invoice->status);
                    @endphp
                    <span class="inline-block px-4 py-2 rounded-lg text-white font-semibold {{ $invoice->status === 'paid' ? 'bg-green-600' : ($invoice->status === 'open' ? 'bg-yellow-600' : 'bg-red-600') }}">
                        {{ $statusLabel }}
                    </span>
                    <div class="mt-3 text-xl font-bold text-gray-900">{{ __('billing.invoice.title') }} {{ $invoice->number }}</div>
                    <div class="text-sm text-gray-600 mt-1">{{ __('billing.invoice.date_label') }}: {{ $invoice->issued_at->format('d/m/Y') }}</div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg mb-3 border-b border-gray-200 pb-2">{{ __('billing.invoice.customer') }}</h3>
                    <div class="text-sm text-gray-800 space-y-1">
                        <div class="font-semibold">{{ $invoice->org_company }}</div>
                        <div>{{ $invoice->org_billing_email }}</div>
                        <div>{{ __('billing.invoice.vat') }}: {{ $invoice->org_vat }}</div>
                        <div>{{ $invoice->org_address }}</div>
                        <div>{{ $invoice->org_city }}, {{ $invoice->org_country }}</div>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg mb-3 border-b border-gray-200 pb-2">{{ __('billing.invoice.details') }}</h3>
                    <div class="text-sm text-gray-800 space-y-1">
                        <div><span class="font-medium">{{ __('billing.invoice.number') }}:</span> {{ $invoice->number }}</div>
                        <div><span class="font-medium">{{ __('billing.invoice.issue_date') }}:</span> {{ $invoice->issued_at->format('d/m/Y') }}</div>
                        <div><span class="font-medium">{{ __('billing.invoice.due_date') }}:</span> {{ $invoice->due_at ? $invoice->due_at->format('d/m/Y') : __('billing.invoice.status_paid') }}</div>
                    </div>
                </div>
            </div>
            <div class="mb-8">
                <h3 class="font-bold text-gray-900 text-lg mb-3 border-b border-gray-200 pb-2">{{ __('billing.invoice.services') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-gray-800 font-semibold border-b border-gray-300">{{ __('billing.invoice.description') }}</th>
                                <th class="px-4 py-3 text-right text-gray-800 font-semibold border-b border-gray-300">{{ __('billing.invoice.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr class="border-b border-gray-200">
                                <td class="px-4 py-3 text-gray-800">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-right text-gray-900 font-medium">{{ number_format($item->total_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div></div>
                <div>
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between text-gray-800">
                                <span>{{ __('billing.invoice.subtotal') }}:</span>
                                <span class="font-medium">{{ number_format($invoice->subtotal_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}</span>
                            </div>
                            @if($invoice->discount_cents)
                            <div class="flex justify-between text-gray-800">
                                <span>{{ __('billing.invoice.discount') }}:</span>
                                <span class="font-medium">-{{ number_format($invoice->discount_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-gray-800">
                                <span>{{ __('billing.invoice.tax') }} (25%):</span>
                                <span class="font-medium">{{ number_format($invoice->tax_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-xl text-gray-900 pt-3 border-t-2 border-gray-300">
                                <span>{{ __('billing.invoice.total') }}:</span>
                                <span>{{ number_format($invoice->total_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}</span>
                            </div>
                        </div>
                        @if($invoice->coupon_code)
                            <div class="mt-4">
                                <span class="inline-block px-3 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-medium">{{ __('billing.invoice.coupon') }}: {{ $invoice->coupon_code }}</span>
                            </div>
                        @endif
                        @if($invoice->promotion_snapshot)
                            <div class="mt-2">
                                <span class="inline-block px-3 py-1 rounded bg-blue-100 text-blue-800 text-xs font-medium">{{ __('billing.invoice.promotion') }}: {{ $invoice->promotion_snapshot['name'] ?? '' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
