<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokemonImportLog extends Model
{
    protected $fillable = [
        'batch_id',
        'set_code',
        'start_page',
        'current_page',
        'total_pages',
        'status',
        'started_at',
        'completed_at',
        'cards_processed',
        'cards_new',
        'cards_updated',
        'cards_failed',
        'failed_cards',
        'pages_completed',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_cards' => 'array',
        'pages_completed' => 'array',
    ];

    /**
     * Marca la pagina come completata
     */
    public function markPageCompleted(int $page): void
    {
        $pages = $this->pages_completed ?? [];
        
        if (!in_array($page, $pages)) {
            $pages[] = $page;
            sort($pages);
            $this->pages_completed = $pages;
            $this->current_page = $page;
            $this->save();
        }
    }

    /**
     * Aggiorna le statistiche
     */
    public function updateStats(array $stats): void
    {
        $this->cards_processed += $stats['processed'] ?? 0;
        $this->cards_new += $stats['new'] ?? 0;
        $this->cards_updated += $stats['updated'] ?? 0;
        $this->cards_failed += $stats['failed'] ?? 0;
        
        if (!empty($stats['failedCards'])) {
            $existingFailed = $this->failed_cards ?? [];
            $this->failed_cards = array_merge($existingFailed, $stats['failedCards']);
        }
        
        $this->save();
    }

    /**
     * Marca come completato
     */
    public function markCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Marca come fallito
     */
    public function markFailed(string $error): void
    {
        $this->status = 'failed';
        $this->completed_at = now();
        $this->error_message = $error;
        $this->save();
    }

    /**
     * Calcola la durata dell'import
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->completed_at ?? now();
        return $this->started_at->diffForHumans($end, true);
    }

    /**
     * Calcola la percentuale di completamento
     */
    public function getProgressPercentageAttribute(): ?float
    {
        if (!$this->total_pages || $this->total_pages == 0) {
            return null;
        }

        $completedPages = count($this->pages_completed ?? []);
        return round(($completedPages / $this->total_pages) * 100, 2);
    }
}
