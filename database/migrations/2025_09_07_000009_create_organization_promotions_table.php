<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationPromotionsTable extends Migration
{
    public function up()
    {
        Schema::create('organization_promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('promotion_id');
            $table->dateTime('redeemed_at');
            $table->string('coupon_code')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'promotion_id', 'redeemed_at'], 'org_promo_redeem_unique');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('organization_promotions');
    }
}
