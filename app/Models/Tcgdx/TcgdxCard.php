<?php

namespace App\Models\Tcgdx;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TcgdxCard extends Model
{
    protected $table = 'tcgdx_cards';

    protected $fillable = [
        'tcgdex_id',
        'set_tcgdx_id',
        'local_id',
        'number',
        'name',
        'rarity',
        'illustrator',
        'image_small_url',
        'image_large_url',
        'types',
        'subtypes',
        'supertype',
        'hp',
        'evolves_from',
        'raw',
    ];

    protected $casts = [
        'name' => 'array',
        'types' => 'array',
        'subtypes' => 'array',
        'raw' => 'array',
        'hp' => 'integer',
    ];

    protected $appends = ['name_en'];

    public function set(): BelongsTo
    {
        return $this->belongsTo(TcgdxSet::class, 'set_tcgdx_id');
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
     * Get high quality image URL (TCGdex format)
     */
    public function getHighQualityImageUrl(): ?string
    {
        if (!$this->image_large_url) {
            return null;
        }
        
        // TCGdex URLs can be extended with /high.webp for better quality
        return $this->image_large_url . '/high.webp';
    }

    /**
     * Get low quality image URL (TCGdex format)
     */
    public function getLowQualityImageUrl(): ?string
    {
        if (!$this->image_small_url) {
            return null;
        }
        
        return $this->image_small_url . '/low.webp';
    }

    /**     * Get localized name (fallback to en)
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        
        if (is_array($this->name)) {
            return $this->name[$locale] ?? $this->name['en'] ?? $this->name[array_key_first($this->name)] ?? 'Unknown';
        }
        
        return $this->name ?? 'Unknown';
    }
}
