<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TcgcsvGroup extends Model
{
    protected $table = 'tcgcsv_groups';
    
    protected $fillable = [
        'category_id',
        'game_id',
        'group_id',
        'name',
        'abbreviation',
        'logo_url',
        'rapidapi_episode_id',
        'tcgdex_set_id',
        'published_on',
        'modified_on',
        'raw',
        'show_in_carousel',
    ];
    
    protected $casts = [
        'category_id' => 'integer',
        'group_id' => 'integer',
        'rapidapi_episode_id' => 'integer',
        'published_on' => 'datetime',
        'modified_on' => 'datetime',
        'raw' => 'array',
        'show_in_carousel' => 'boolean',
    ];
    
    public function products(): HasMany
    {
        return $this->hasMany(TcgcsvProduct::class, 'group_id', 'group_id');
    }
    
    public function prices(): HasMany
    {
        return $this->hasMany(TcgcsvPrice::class, 'group_id', 'group_id');
    }
    
    public function rapidapiEpisode(): BelongsTo
    {
        return $this->belongsTo(RapidapiEpisode::class, 'rapidapi_episode_id', 'episode_id');
    }
    
    /**
     * Many-to-many relationship with RapidAPI episodes
     * Allows a group to be mapped to multiple episodes (e.g., Latias & Latios)
     */
    public function rapidapiEpisodes(): BelongsToMany
    {
        return $this->belongsToMany(
            RapidapiEpisode::class,
            'tcgcsv_group_rapidapi_episode',
            'tcgcsv_group_id',
            'rapidapi_episode_id',
            'id',
            'episode_id'
        )->withTimestamps();
    }
}
