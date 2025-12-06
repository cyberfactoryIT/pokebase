<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricingPlansTable extends Migration
{
    public function up()
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->integer('monthly_price_cents')->default(0);
            $table->integer('yearly_price_cents')->nullable();
            $table->string('currency')->default('EUR');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('pricing_plans');
    }
}
