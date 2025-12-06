<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionPricingPlanTable extends Migration
{
    public function up()
    {
        Schema::create('promotion_pricing_plan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('pricing_plan_id');
            $table->timestamps();

            $table->unique(['promotion_id', 'pricing_plan_id'], 'promo_plan_unique');
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('promotion_pricing_plan');
    }
}
