<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturePlanTable extends Migration
{
    public function up()
    {
        Schema::create('feature_plan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pricing_plan_id');
            $table->unsignedBigInteger('feature_id');
            $table->string('value')->nullable();
            $table->timestamps();

            $table->unique(['pricing_plan_id', 'feature_id'], 'feature_plan_unique');
            $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('cascade');
            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('feature_plan');
    }
}
