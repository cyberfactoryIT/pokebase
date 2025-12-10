<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokemonSet extends Model
{
    protected $fillable = [
        'set_id',
        'name',
        'series',
        'printed_total',
        'total',
        'ptcgo_code',
        'release_date',
        'api_updated_at',
        'symbol_url',
        'logo_url',
        'legalities',
        'last_import_at',
        'last_import_batch_id',
        'last_import_status',
        'cards_imported',
        'last_import_error',
    ];

    protected $casts = [
        'release_date' => 'date',
        'api_updated_at' => 'datetime',
        'last_import_at' => 'datetime',
        'legalities' => 'array',
    ];

    /**
     * Marca l'import come iniziato
     */
    public function startImport(string $batchId): void
    {
        $this->last_import_batch_id = $batchId;
        $this->last_import_status = 'in_progress';
        $this->last_import_at = now();
        $this->last_import_error = null;
        $this->save();
    }

    /**
     * Marca l'import come completato
     */
    public function completeImport(int $cardsImported): void
    {
        $this->last_import_status = 'success';
        $this->cards_imported = $cardsImported;
        $this->save();
    }

    /**
     * Marca l'import come fallito
     */
    public function failImport(string $error): void
    {
        $this->last_import_status = 'failed';
        $this->last_import_error = $error;
        $this->save();
    }

    /**
     * Scope per set mai importati
     */
    public function scopeNeverImported($query)
    {
        return $query->where('last_import_status', 'never');
    }

    /**
     * Scope per set con import fallito
     */
    public function scopeFailedImport($query)
    {
        return $query->where('last_import_status', 'failed');
    }

    /**
     * Scope per set con import in corso
     */
    public function scopeInProgress($query)
    {
        return $query->where('last_import_status', 'in_progress');
    }

    /**
     * Scope per set importati con successo
     */
    public function scopeSuccessful($query)
    {
        return $query->where('last_import_status', 'success');
    }

    /**
     * Controlla se ha bisogno di un reimport (API aggiornata dopo import)
     */
    public function needsReimport(): bool
    {
        if (!$this->last_import_at || !$this->api_updated_at) {
            return false;
        }

        return $this->api_updated_at->isAfter($this->last_import_at);
    }
}
