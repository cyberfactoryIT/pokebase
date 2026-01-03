<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipelineRun extends Model
{
    protected $fillable = [
        'task_name',
        'status',
        'started_at',
        'finished_at',
        'rows_processed',
        'rows_created',
        'rows_updated',
        'rows_deleted',
        'errors_count',
        'error_message',
        'error_details',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'error_details' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Start a new pipeline run
     */
    public static function start(string $taskName, array $metadata = []): self
    {
        return self::create([
            'task_name' => $taskName,
            'status' => 'running',
            'started_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Mark run as successful
     */
    public function markSuccess(array $stats = []): void
    {
        $this->update([
            'status' => 'success',
            'finished_at' => now(),
            'rows_processed' => $stats['rows_processed'] ?? null,
            'rows_created' => $stats['rows_created'] ?? null,
            'rows_updated' => $stats['rows_updated'] ?? null,
            'rows_deleted' => $stats['rows_deleted'] ?? null,
            'errors_count' => $stats['errors_count'] ?? 0,
        ]);
    }

    /**
     * Mark run as failed
     */
    public function markFailed(string $errorMessage, array $errorDetails = []): void
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $errorMessage,
            'error_details' => $errorDetails,
            'errors_count' => $this->errors_count + 1,
        ]);
    }

    /**
     * Update processing statistics
     */
    public function updateStats(array $stats): void
    {
        $this->update([
            'rows_processed' => $stats['rows_processed'] ?? $this->rows_processed,
            'rows_created' => $stats['rows_created'] ?? $this->rows_created,
            'rows_updated' => $stats['rows_updated'] ?? $this->rows_updated,
            'rows_deleted' => $stats['rows_deleted'] ?? $this->rows_deleted,
        ]);
    }

    /**
     * Get duration in human-readable format
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->finished_at) {
            return null;
        }

        $seconds = $this->duration_seconds;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        return "{$seconds}s";
    }

    /**
     * Scope: Get recent runs for a task
     */
    public function scopeForTask($query, string $taskName)
    {
        return $query->where('task_name', $taskName);
    }

    /**
     * Scope: Get successful runs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Get failed runs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get running tasks
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope: Recent runs (last N days)
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }
}
