<?php

namespace App\Services;

use App\Models\User;
use App\Models\Invoice;
use App\Models\DeckEvaluationPurchase;
use Illuminate\Support\Collection;

/**
 * TransactionHistoryService
 * 
 * Aggregates membership payments and deck evaluation purchases
 * into a unified transaction history for display in user profile.
 */
class TransactionHistoryService
{
    /**
     * Get unified transaction history for a user
     * 
     * @param User $user
     * @return Collection Sorted collection of transactions (newest first)
     */
    public function getHistory(User $user): Collection
    {
        $transactions = collect();

        // Add membership invoices if organizations enabled
        if (config('organizations.enabled') && $user->organization) {
            $invoices = Invoice::where('organization_id', $user->organization_id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($invoices as $invoice) {
                $transactions->push([
                    'id' => 'invoice_' . $invoice->id,
                    'date' => $invoice->issued_at ?? $invoice->created_at,
                    'type' => 'membership',
                    'description' => $invoice->org_name ?? 'Membership Subscription',
                    'amount' => $invoice->total_cents,
                    'currency' => $invoice->currency ?? 'EUR',
                    'status' => $invoice->status ?? 'paid',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                    'payment_reference' => null,
                    'model' => $invoice,
                ]);
            }
        }

        // Add deck evaluation purchases
        $purchases = DeckEvaluationPurchase::where('user_id', $user->id)
            ->with('package')
            ->orderBy('purchased_at', 'desc')
            ->get();

        foreach ($purchases as $purchase) {
            $transactions->push([
                'id' => 'deck_eval_' . $purchase->id,
                'date' => $purchase->purchased_at,
                'type' => 'deck_evaluation',
                'description' => $purchase->package->name ?? 'Deck Evaluation Package',
                'amount' => $purchase->package->price_cents ?? 0,
                'currency' => $purchase->package->currency ?? 'EUR',
                'status' => $this->mapPurchaseStatus($purchase),
                'invoice_id' => null,
                'invoice_number' => null,
                'payment_reference' => $purchase->payment_reference,
                'model' => $purchase,
            ]);
        }

        // Sort by date descending
        return $transactions->sortByDesc('date')->values();
    }

    /**
     * Map deck evaluation purchase status to transaction status
     */
    private function mapPurchaseStatus(DeckEvaluationPurchase $purchase): string
    {
        if ($purchase->status === 'active') {
            return 'paid';
        }

        if ($purchase->status === 'expired') {
            return 'expired';
        }

        if ($purchase->status === 'consumed') {
            return 'completed';
        }

        return 'pending';
    }

    /**
     * Get formatted amount with currency symbol
     */
    public function formatAmount(int $amountCents, string $currency = 'EUR'): string
    {
        $amount = $amountCents / 100;
        
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'DKK' => 'kr',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        
        if (in_array($currency, ['DKK'])) {
            return number_format($amount, 2, ',', '.') . ' ' . $symbol;
        }

        return $symbol . number_format($amount, 2, '.', ',');
    }

    /**
     * Check if invoice exists for transaction
     */
    public function hasInvoice(array $transaction): bool
    {
        return !empty($transaction['invoice_id']);
    }

    /**
     * Get invoice download URL
     */
    public function getInvoiceUrl(array $transaction): ?string
    {
        if (!$this->hasInvoice($transaction)) {
            return null;
        }

        return route('billing.invoice.show', $transaction['invoice_id']);
    }
}
