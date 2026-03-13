<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('onboarding_tour_completed_at')->nullable()->after('privacy_version');
            $table->timestamp('onboarding_tour_skipped_at')->nullable()->after('onboarding_tour_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_tour_completed_at', 'onboarding_tour_skipped_at']);
        });
    }
};

