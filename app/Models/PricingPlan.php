<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PricingPlan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name','code','monthly_price_cents','yearly_price_cents','currency','meta'
    ];
    protected $casts = [
        'meta' => 'array',
    ];

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_plan')
            ->withPivot('value')->withTimestamps();
    }

    public function getFeatureValue(string $key, $default = null)
    {
        $feature = $this->features()->where('key', $key)->first();
        return $feature ? $feature->pivot->value : $default;
    }
}
