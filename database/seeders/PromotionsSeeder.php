<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use App\Models\PricingPlan;

class PromotionsSeeder extends Seeder
{
    public function run()
    {
        $promo = Promotion::firstOrCreate([
            'name' => 'Black Friday 30%',
            'type' => 'percent',
            'value' => 3000,
            'code' => 'BLACKFRIDAY',
            'active' => true,
        ]);
        $plans = PricingPlan::whereIn('code', ['pro','large'])->get();
        foreach ($plans as $plan) {
            $promo->plans()->syncWithoutDetaching([$plan->id]);
        }
    }
}
