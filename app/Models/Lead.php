<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'email',
        'deck_name',
        'guest_deck_id',
        'consent_marketing',
    ];

    protected $casts = [
        'consent_marketing' => 'boolean',
    ];

    public function guestDeck(): BelongsTo
    {
        return $this->belongsTo(GuestDeck::class);
    }

    public function deckValuations(): HasMany
    {
        return $this->hasMany(DeckValuation::class);
    }
}
