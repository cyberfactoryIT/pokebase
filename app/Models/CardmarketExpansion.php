<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardmarketExpansion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cardmarket_expansion_id',
        'name',
        'tcgcsv_group_id',
    ];

    protected $casts = [
        'cardmarket_expansion_id' => 'integer',
    ];

    /**
     * Get all products for this expansion.
     */
    public function products()
    {
        return $this->hasMany(CardmarketProduct::class, 'id_expansion', 'cardmarket_expansion_id');
    }
}
