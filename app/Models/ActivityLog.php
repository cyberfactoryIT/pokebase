<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'type',
        'action',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Registra un evento nell'activity log.
     */
    public static function logActivity($type, $action, $data = [], $organization_id = null, $user_id = null)
    {
        return self::create([
            'type' => $type,
            'action' => $action,
            'data' => $data,
            'organization_id' => $organization_id,
            'user_id' => $user_id,
        ]);
    }
}
