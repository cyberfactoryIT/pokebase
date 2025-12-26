<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TcgcsvGroup extends Model
{
    protected $table = 'tcgcsv_groups';
    
    protected $fillable = [
        'category_id',
        'game_id',
        'group_id',
        'name',
        'abbreviation',
        'published_on',
        'modified_on',
        'raw',
    ];
    
    protected $casts = [
        'category_id' => 'integer',
        'group_id' => 'integer',
        'published_on' => 'datetime',
        'modified_on' => 'datetime',
        'raw' => 'array',
    ];
    
    public function products(): HasMany
    {
        return $this->hasMany(TcgcsvProduct::class, 'group_id', 'group_id');
    }
    
    public function prices(): HasMany
    {
        return $this->hasMany(TcgcsvPrice::class, 'group_id', 'group_id');
    }
}
