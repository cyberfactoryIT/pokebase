<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h1 class="text-3xl font-bold text-white mb-6">
                    {{ __('deck_evaluation.account.title') }}
                </h1>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-900/30 border border-emerald-500/50 rounded-lg">
                        <p class="text-emerald-200">{{ session('success') }}</p>
                    </div>
                @endif

                @if(Auth::user()->deckEvaluationPurchases->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-400 text-lg mb-4">{{ __('deck_evaluation.account.no_purchases') }}</p>
                        <a href="{{ route('deck-evaluation.packages.index') }}" 
                           class="inline-block px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-semibold">
                            {{ __('deck_evaluation.packages.title') }}
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach(Auth::user()->deckEvaluationPurchases()->with('package')->latest()->get() as $purchase)
                            <div class="bg-gray-700 rounded-lg p-6 border {{ $purchase->isActive() ? 'border-emerald-500' : 'border-gray-600' }}">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-white">{{ $purchase->package->name }}</h3>
                                        <p class="text-gray-400 text-sm">{{ $purchase->package->code }}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $purchase->status === 'active' ? 'bg-emerald-900/50 text-emerald-400 border border-emerald-500' : '' }}
                                        {{ $purchase->status === 'expired' ? 'bg-red-900/50 text-red-400 border border-red-500' : '' }}
                                        {{ $purchase->status === 'consumed' ? 'bg-gray-900/50 text-gray-400 border border-gray-500' : '' }}">
                                        {{ __('deck_evaluation.account.status_' . $purchase->status) }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-white">
                                    <div>
                                        <div class="text-sm text-gray-400">{{ __('deck_evaluation.account.purchased_on') }}</div>
                                        <div class="font-semibold">{{ $purchase->purchased_at->format('Y-m-d') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-400">{{ __('deck_evaluation.account.expires_on') }}</div>
                                        <div class="font-semibold">{{ $purchase->expires_at->format('Y-m-d') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-400">{{ __('deck_evaluation.packages.cards_remaining') }}</div>
                                        <div class="font-semibold">
                                            @if($purchase->cards_limit === null)
                                                {{ __('deck_evaluation.packages.unlimited') }}
                                            @else
                                                {{ $purchase->remaining_cards }} / {{ $purchase->cards_limit }}
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-400">{{ __('deck_evaluation.account.cards_used') }}</div>
                                        <div class="font-semibold">{{ $purchase->cards_used }}</div>
                                    </div>
                                </div>

                                @if($purchase->payment_reference)
                                    <div class="mt-4 pt-4 border-t border-gray-600">
                                        <span class="text-xs text-gray-500">{{ __('deck_evaluation.purchase.payment_reference') }}: {{ $purchase->payment_reference }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
