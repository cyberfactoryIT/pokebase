<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DeckCardPhoto extends Model
{
    protected $fillable = [
        'user_id',
        'deck_card_id',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    /**
     * Get the user who uploaded this photo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the deck card this photo belongs to
     */
    public function deckCard(): BelongsTo
    {
        return $this->belongsTo(DeckCard::class, 'deck_card_id');
    }

    /**
     * Get file size in human readable format
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete the photo file when the model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            if ($photo->path && Storage::disk('local')->exists($photo->path)) {
                Storage::disk('local')->delete($photo->path);
            }
        });
    }
}
