@extends('layouts.app')

@section('content')

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto">
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">{{ __('messages.allinvoices') }}</h2>
                <div class="flex gap-2">
                    @if(config('organizations.enabled'))
                    <form method="GET" action="" class="flex items-center gap-2">
                        <label for="organization_id" class="font-semibold text-sm">{{ __('messages.organization') }}</label>
                        <x-select id="organization_id" name="organization_id" :options="App\Models\Organization::orderBy('name')->pluck('name','id')->toArray()" placeholder="{{ __('messages.all') }}" :value="request('organization_id')" />
                        <x-button type="submit" icon="search" variant="primary">{{ __('messages.filter') }}</x-button>
                    </form>
                    <x-button as="a" href="{{ route('superadmin.billing.invoices.export', array_filter(['organization_id' => request('organization_id')])) }}" icon="download" target="_blank" variant="success">
                        {{ __('messages.export_csv') }}
                    </x-button>
                    @else
                    <x-button as="a" href="{{ route('superadmin.billing.invoices.export') }}" icon="download" target="_blank" variant="success">
                        {{ __('messages.export_csv') }}
                    </x-button>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <x-table>
            <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('messages.number') }}</th>
                @if(config('organizations.enabled'))
                <th class="px-4 py-2 text-left">{{ __('messages.organization') }}</th>
                @endif
                            <th class="px-4 py-2 text-left">{{ __('messages.issued') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.status') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('messages.total') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
            @foreach($invoices as $invoice)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $invoice->number }}</td>
                @if(config('organizations.enabled'))
                <td class="px-4 py-2">{{ optional($invoice->organization)->name ?? $invoice->organization_id }}</td>
                @endif
                                <td class="px-4 py-2">{{ $invoice->issued_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">
                                    <x-badge variant="{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'open' ? 'warning' : 'danger') }}">
                                        {{ $invoice->status === 'paid' ? __('messages.status_paid') : ($invoice->status === 'open' ? __('messages.status_due') : __('messages.status_overdue')) }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-2"><x-money :cents="$invoice->total_cents" :currency="$invoice->currency" /></td>
                                <td class="px-4 py-2 text-center flex gap-2 justify-center">
                                    <x-button as="a" href="{{ route('superadmin.billing.invoice.show', $invoice) }}" icon="eye" target="_blank" variant="primary" title="{{ __('messages.view_invoice') }}">
                                        <span class="sr-only">{{ __('messages.view_invoice') }}</span>
                                        <span class="hidden sm:inline">{{ __('messages.view') }}</span>
                                    </x-button>
                                    <x-button as="a" href="{{ route('billing.invoice.receipt', $invoice) }}" icon="file-pdf" target="_blank" variant="danger" title="{{ __('messages.download_receipt') }}">
                                        <span class="sr-only">{{ __('messages.download_receipt') }}</span>
                                        <span class="hidden sm:inline">{{ __('messages.receipt') }}</span>
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-right text-sm text-gray-500">
                                {{ __('messages.showing_invoices', ['count' => $invoices->count(), 'total' => $invoices->total()]) }}
                            </td>
                        </tr>
                    </tfoot>
                </x-table>
            </div>
            <div class="mt-4">
                {{ $invoices->links() }}
            </div>
        </x-card>
    </div>
</div>
@endsection
