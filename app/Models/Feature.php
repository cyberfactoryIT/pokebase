<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = [
        'key','name','description','value_type'
    ];

    public function plans()
    {
        return $this->belongsToMany(PricingPlan::class, 'feature_plan')
            ->withPivot('value')->withTimestamps();
    }
}
