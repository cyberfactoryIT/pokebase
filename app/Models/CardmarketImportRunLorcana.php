<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CardmarketImportRunLorcana extends Model
{
    protected $table = 'cardmarket_import_runs_lorcana';

    protected $fillable = [
        'run_uuid',
        'type',
        'status',
        'rows_read',
        'rows_upserted',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'rows_read' => 'integer',
        'rows_upserted' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->run_uuid)) {
                $model->run_uuid = (string) Str::uuid();
            }
            if (empty($model->started_at)) {
                $model->started_at = now();
            }
        });
    }

    /**
     * Mark the run as running.
     */
    public function markRunning(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the run as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the run as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Scope to get recent runs.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Scope to get successful runs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get failed runs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
