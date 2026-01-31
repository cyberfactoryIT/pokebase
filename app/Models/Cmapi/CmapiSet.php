<?php

namespace App\Models\Cmapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmapiSet extends Model
{
    protected $table = 'cmapi_sets';

    protected $fillable = [
        'cmapi_id',
        'game',
        'cmapi_episode',
        'name',
        'code',
        'logo_url',
        'release_date',
        'card_count',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'release_date' => 'date',
        'card_count' => 'integer',
    ];

    /**
     * Cards in this set
     */
    public function cards(): HasMany
    {
        return $this->hasMany(CmapiCard::class, 'set_cmapi_id');
    }
}
