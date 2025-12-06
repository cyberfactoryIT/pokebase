<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use App\Models\PricingPlan;

class PlansAndFeaturesSeeder extends Seeder
{
    public function run()
    {
        $features = [
            ['key'=>'timesheets','name'=>'Timesheets','value_type'=>'bool'],
            ['key'=>'approvals','name'=>'Approvals','value_type'=>'bool'],
            ['key'=>'reports_export','name'=>'Reports Export','value_type'=>'bool'],
            ['key'=>'audit_log','name'=>'Audit Log','value_type'=>'bool'],
            ['key'=>'projects_limit','name'=>'Projects Limit','value_type'=>'int'],
            ['key'=>'users_limit','name'=>'Users Limit','value_type'=>'int'],
        ];
        foreach ($features as $f) {
            Feature::firstOrCreate(['key'=>$f['key']], $f);
        }

        $plans = [
            ['name'=>'Freemium','code'=>'free','monthly_price_cents'=>0,'currency'=>'EUR'],
            ['name'=>'Pro','code'=>'pro','monthly_price_cents'=>9900,'currency'=>'EUR'],
            ['name'=>'Large','code'=>'large','monthly_price_cents'=>29900,'currency'=>'EUR'],
        ];
        foreach ($plans as $p) {
            $plan = PricingPlan::firstOrCreate(['code'=>$p['code']], $p);
            // Assign features
            foreach (Feature::all() as $feature) {
                $value = match($feature->key) {
                    'projects_limit' => $plan->code === 'free' ? 3 : ($plan->code === 'pro' ? 20 : 100),
                    'users_limit' => $plan->code === 'free' ? 5 : ($plan->code === 'pro' ? 50 : 500),
                    default => $plan->code !== 'free' ? '1' : '0',
                };
                $plan->features()->syncWithoutDetaching([$feature->id => ['value' => $value]]);
            }
        }
    }
}
