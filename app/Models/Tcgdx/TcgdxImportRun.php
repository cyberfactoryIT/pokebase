<?php

namespace App\Models\Tcgdx;

use Illuminate\Database\Eloquent\Model;

class TcgdxImportRun extends Model
{
    protected $table = 'tcgdx_import_runs';

    protected $fillable = [
        'started_at',
        'finished_at',
        'status',
        'scope',
        'stats',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'stats' => 'array',
    ];

    /**
     * Mark run as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark run as success
     */
    public function markAsSuccess(?array $stats = null): void
    {
        $this->update([
            'status' => 'success',
            'finished_at' => now(),
            'stats' => $stats ?? $this->stats,
        ]);
    }

    /**
     * Add to stats
     */
    public function addStats(array $data): void
    {
        $currentStats = $this->stats ?? [];
        $this->update([
            'stats' => array_merge($currentStats, $data),
        ]);
    }
}
