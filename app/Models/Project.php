<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $responsible_user_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_active
 * @property bool $billable
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 */
class Project extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id','responsible_user_id','name','code','description',
        'is_active','billable','starts_at','ends_at'
    ];

    protected $casts = [
        'is_active' => 'bool',
        'billable' => 'bool',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function scopeForOrg(Builder $q, ?int $orgId)
    {
        if (is_null($orgId)) {
            return $q;
        }
        return $q->where('organization_id', $orgId);
    }

    public static function nextCodeForOrg(int $orgId): string
    {
        return 'PRJ-' . Str::upper(Str::random(6));
    }
}
