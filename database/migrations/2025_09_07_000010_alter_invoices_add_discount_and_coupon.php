<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesAddDiscountAndCoupon extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('discount_cents')->default(0)->after('tax_cents');
            $table->string('coupon_code')->nullable()->after('discount_cents');
            $table->json('promotion_snapshot')->nullable()->after('coupon_code');
        });
    }
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_cents','coupon_code','promotion_snapshot']);
        });
    }
}
