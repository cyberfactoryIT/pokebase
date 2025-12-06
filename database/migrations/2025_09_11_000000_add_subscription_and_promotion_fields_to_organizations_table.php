<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->date('subscription_date')->nullable();
            $table->date('renew_date')->nullable();
            $table->date('end_promotion_date')->nullable();
            $table->string('promotion_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['subscription_date', 'renew_date', 'end_promotion_date', 'promotion_code']);
        });
    }
};
