<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeckValuation extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'guest_deck_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function guestDeck(): BelongsTo
    {
        return $this->belongsTo(GuestDeck::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeckValuationItem::class);
    }
}
