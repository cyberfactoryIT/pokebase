<?php

namespace App\Models\Tcgdx;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TcgdxSet extends Model
{
    protected $table = 'tcgdx_sets';

    protected $fillable = [
        'tcgdex_id',
        'game_id',
        'name',
        'series',
        'logo_url',
        'symbol_url',
        'release_date',
        'card_count_total',
        'card_count_official',
        'raw',
    ];

    protected $casts = [
        'name' => 'array',
        'raw' => 'array',
        'release_date' => 'date',
        'card_count_total' => 'integer',
        'card_count_official' => 'integer',
        'game_id' => 'integer',
    ];

    protected $appends = ['name_en', 'series_name', 'released_at'];

    public function cards(): HasMany
    {
        return $this->hasMany(TcgdxCard::class, 'set_tcgdx_id');
    }

    /**
     * Get English name (accessor)
     */
    public function getNameEnAttribute(): string
    {
        // name is already cast to array by Eloquent
        if (is_array($this->name)) {
            return $this->name['en'] ?? $this->name[array_key_first($this->name)] ?? 'Unknown';
        }
        
        return $this->name ?? 'Unknown';
    }

    /**
     * Get localized name (fallback to en)
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        
        if (is_array($this->name)) {
            return $this->name[$locale] ?? $this->name['en'] ?? $this->name[array_key_first($this->name)] ?? 'Unknown';
        }
        
        return $this->name ?? 'Unknown';
    }
    
    /**
     * Get series_name attribute (accessor for compatibility)
     * TCGDEX stores series as string, but we need it as array like name
     */
    public function getSeriesNameAttribute()
    {
        if ($this->series) {
            return ['en' => $this->series];
        }
        return ['en' => 'N/A'];
    }
    
    /**
     * Get released_at attribute (alias for release_date)
     */
    public function getReleasedAtAttribute()
    {
        return $this->release_date;
    }
}
