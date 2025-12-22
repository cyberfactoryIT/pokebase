<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcgcsvImportLog extends Model
{
    protected $table = 'tcgcsv_import_logs';
    
    protected $fillable = [
        'batch_id',
        'type',
        'run_id',
        'status',
        'message',
        'started_at',
        'completed_at',
        'duration_ms',
        'groups_processed',
        'groups_new',
        'groups_updated',
        'groups_failed',
        'products_processed',
        'products_new',
        'products_updated',
        'products_failed',
        'prices_processed',
        'prices_new',
        'prices_updated',
        'prices_failed',
        'groups_completed',
        'error_details',
        'options',
        'metrics',
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'groups_completed' => 'array',
        'error_details' => 'array',
        'options' => 'array',
        'metrics' => 'array',
    ];
    
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at) {
            return null;
        }
        
        $end = $this->completed_at ?? now();
        $diff = $this->started_at->diffInSeconds($end);
        
        if ($diff < 60) {
            return $diff . 's';
        } elseif ($diff < 3600) {
            return round($diff / 60, 1) . 'm';
        } else {
            return round($diff / 3600, 1) . 'h';
        }
    }
    
    public function getTotalProcessedAttribute(): int
    {
        return $this->groups_processed + $this->products_processed + $this->prices_processed;
    }
    
    public function getTotalNewAttribute(): int
    {
        return $this->groups_new + $this->products_new + $this->prices_new;
    }
    
    public function getTotalFailedAttribute(): int
    {
        return $this->groups_failed + $this->products_failed + $this->prices_failed;
    }
}
