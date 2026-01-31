<?php

namespace App\Models\Cmapi;

use Illuminate\Database\Eloquent\Model;

class CmapiImportRun extends Model
{
    protected $table = 'cmapi_import_runs';

    protected $fillable = [
        'game',
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
     * Start a new import run
     */
    public static function start(string $game, string $scope, array $initialStats = []): self
    {
        return self::create([
            'game' => $game,
            'started_at' => now(),
            'status' => 'running',
            'scope' => $scope,
            'stats' => $initialStats,
        ]);
    }

    /**
     * Mark import as successful
     */
    public function markAsSuccess(array $stats = []): void
    {
        $this->update([
            'status' => 'success',
            'finished_at' => now(),
            'stats' => $stats,
        ]);
    }

    /**
     * Mark import as failed
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
     * Add/update stats
     */
    public function addStats(array $stats): void
    {
        $this->update([
            'stats' => array_merge($this->stats ?? [], $stats),
        ]);
    }
}
