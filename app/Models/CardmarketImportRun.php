<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CardmarketImportRun extends Model
{
    protected $fillable = [
        'run_uuid',
        'type',
        'status',
        'started_at',
        'finished_at',
        'source_catalogue_version',
        'source_priceguide_version',
        'rows_read',
        'rows_upserted',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'rows_read' => 'integer',
        'rows_upserted' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($run) {
            if (empty($run->run_uuid)) {
                $run->run_uuid = (string) Str::uuid();
            }
            if (empty($run->started_at)) {
                $run->started_at = now();
            }
        });
    }

    /**
     * Mark the run as successful.
     */
    public function markSuccess(): void
    {
        $this->update([
            'status' => 'success',
            'finished_at' => now(),
        ]);
    }

    /**
     * Mark the run as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Increment rows read counter.
     */
    public function incrementRowsRead(int $count = 1): void
    {
        $this->increment('rows_read', $count);
    }

    /**
     * Increment rows upserted counter.
     */
    public function incrementRowsUpserted(int $count = 1): void
    {
        $this->increment('rows_upserted', $count);
    }

    /**
     * Get the duration of the run in seconds.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }

        return $this->finished_at->diffInSeconds($this->started_at);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get recent runs.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest('started_at')->limit($limit);
    }

    /**
     * Scope to get successful runs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed runs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get running runs.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }
}
