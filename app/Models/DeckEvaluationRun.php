<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckEvaluationRun extends Model
{
    protected $fillable = [
        'session_id',
        'purchase_id',
        'run_hash',
        'cards_count',
        'evaluated_at',
        'meta',
    ];

    protected $casts = [
        'cards_count' => 'integer',
        'evaluated_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Get the session
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(DeckEvaluationSession::class, 'session_id');
    }

    /**
     * Get the purchase
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(DeckEvaluationPurchase::class, 'purchase_id');
    }

    /**
     * Generate a hash for a set of cards (for idempotency)
     */
    public static function generateRunHash(array $cardProductIds): string
    {
        sort($cardProductIds);
        return hash('sha256', implode(',', $cardProductIds));
    }
}
