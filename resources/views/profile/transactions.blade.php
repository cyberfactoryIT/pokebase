@extends('layouts.app')

@section('page_title', __('transactions.title'))

@section('content')
<div class="max-w-6xl mx-auto">
    
    <!-- Tab Navigation -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
        <div class="flex border-b border-white/10">
            <a href="{{ route('profile.edit') }}" 
               class="px-6 py-4 text-gray-400 hover:text-white transition border-b-2 border-transparent">
                <i class="fa fa-user mr-2"></i>{{ __('profile/edit.tab_profile') }}
            </a>
            <a href="{{ route('profile.subscription') }}" 
               class="px-6 py-4 text-gray-400 hover:text-white transition border-b-2 border-transparent">
                <i class="fa fa-credit-card mr-2"></i>{{ __('profile/edit.tab_subscription') }}
            </a>
            <a href="{{ route('profile.transactions') }}" 
               class="px-6 py-4 text-white transition border-b-2 border-blue-500">
                <i class="fa fa-receipt mr-2"></i>{{ __('profile/edit.tab_transactions') }}
            </a>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ __('transactions.title') }}</h2>
                <p class="text-gray-400 text-sm mt-1">{{ __('transactions.explanation') }}</p>
            </div>
            @if($transactions->isNotEmpty())
                <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-sm font-semibold">
                    {{ $transactions->count() }} {{ __('transactions.title') }}
                </span>
            @endif
        </div>

        @if($transactions->isEmpty())
            <div class="text-center py-12">
                <i class="fa fa-receipt text-gray-600 text-5xl mb-4"></i>
                <p class="text-gray-400">{{ __('transactions.no_transactions') }}</p>
            </div>
        @else
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 text-left">
                            <th class="pb-3 text-gray-400 font-semibold text-sm">{{ __('transactions.date') }}</th>
                            <th class="pb-3 text-gray-400 font-semibold text-sm">{{ __('transactions.type') }}</th>
                            <th class="pb-3 text-gray-400 font-semibold text-sm">{{ __('transactions.description') }}</th>
                            <th class="pb-3 text-gray-400 font-semibold text-sm text-right">{{ __('transactions.amount') }}</th>
                            <th class="pb-3 text-gray-400 font-semibold text-sm text-center">{{ __('transactions.status') }}</th>
                            <th class="pb-3 text-gray-400 font-semibold text-sm text-center">{{ __('transactions.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                <td class="py-4 text-gray-300">
                                    {{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}
                                </td>
                                <td class="py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $transaction['type'] === 'membership' ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400' }}">
                                        {{ __('transactions.type_' . $transaction['type']) }}
                                    </span>
                                </td>
                                <td class="py-4 text-white">
                                    {{ $transaction['description'] }}
                                </td>
                                <td class="py-4 text-white text-right font-semibold">
                                    {{ $historyService->formatAmount($transaction['amount'], $transaction['currency']) }}
                                </td>
                                <td class="py-4 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $transaction['status'] === 'paid' ? 'bg-green-500/20 text-green-400' : '' }}
                                        {{ $transaction['status'] === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                        {{ $transaction['status'] === 'failed' ? 'bg-red-500/20 text-red-400' : '' }}
                                        {{ $transaction['status'] === 'expired' ? 'bg-gray-500/20 text-gray-400' : '' }}
                                        {{ $transaction['status'] === 'completed' ? 'bg-blue-500/20 text-blue-400' : '' }}">
                                        {{ __('transactions.status_' . $transaction['status']) }}
                                    </span>
                                </td>
                                <td class="py-4 text-center">
                                    @if($historyService->hasInvoice($transaction))
                                        <a href="{{ $historyService->getInvoiceUrl($transaction) }}" 
                                           class="inline-flex items-center gap-2 px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium transition">
                                            <i class="fa fa-file-invoice"></i>
                                            {{ __('transactions.view_invoice') }}
                                        </a>
                                    @else
                                        @if($transaction['payment_reference'])
                                            <span class="text-gray-500 text-xs" title="{{ __('transactions.payment_ref') }}">
                                                {{ substr($transaction['payment_reference'], -6) }}
                                            </span>
                                        @else
                                            <span class="text-gray-600 text-xs">
                                                {{ __('transactions.invoice_not_available') }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden space-y-4">
                @foreach($transactions as $transaction)
                    <div class="p-4 bg-black/30 rounded-lg border border-white/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $transaction['type'] === 'membership' ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400' }}">
                                {{ __('transactions.type_' . $transaction['type']) }}
                            </span>
                            <span class="text-gray-400 text-sm">
                                {{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="text-white font-semibold mb-1">{{ $transaction['description'] }}</div>
                            <div class="text-gray-300 font-semibold">
                                {{ $historyService->formatAmount($transaction['amount'], $transaction['currency']) }}
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $transaction['status'] === 'paid' ? 'bg-green-500/20 text-green-400' : '' }}
                                {{ $transaction['status'] === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                {{ $transaction['status'] === 'failed' ? 'bg-red-500/20 text-red-400' : '' }}
                                {{ $transaction['status'] === 'expired' ? 'bg-gray-500/20 text-gray-400' : '' }}
                                {{ $transaction['status'] === 'completed' ? 'bg-blue-500/20 text-blue-400' : '' }}">
                                {{ __('transactions.status_' . $transaction['status']) }}
                            </span>
                            
                            @if($historyService->hasInvoice($transaction))
                                <a href="{{ $historyService->getInvoiceUrl($transaction) }}" 
                                   class="inline-flex items-center gap-2 px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium transition">
                                    <i class="fa fa-file-invoice"></i>
                                    {{ __('transactions.view_invoice') }}
                                </a>
                            @elseif($transaction['payment_reference'])
                                <span class="text-gray-500 text-xs">
                                    Ref: {{ substr($transaction['payment_reference'], -6) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
