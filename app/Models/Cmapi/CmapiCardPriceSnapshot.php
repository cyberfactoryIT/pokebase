<?php

namespace App\Models\Cmapi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmapiCardPriceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'cmapi_card_id',
        'price_eur',
        'price_usd',
        'language',
        'condition',
        'recorded_at',
    ];

    protected $casts = [
        'price_eur' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(CmapiCard::class, 'cmapi_card_id');
    }
}
