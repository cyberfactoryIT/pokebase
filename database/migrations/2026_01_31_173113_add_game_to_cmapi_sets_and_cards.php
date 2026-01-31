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
        Schema::table('cmapi_sets', function (Blueprint $table) {
            $table->string('game', 20)->default('lorcana')->after('cmapi_id')->index();
            $table->integer('cmapi_episode')->nullable()->after('cmapi_id')->index();
        });

        Schema::table('cmapi_cards', function (Blueprint $table) {
            $table->string('game', 20)->default('lorcana')->after('cmapi_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cmapi_sets', function (Blueprint $table) {
            $table->dropIndex(['game']);
            $table->dropColumn('game');
            $table->dropIndex(['cmapi_episode']);
            $table->dropColumn('cmapi_episode');
        });

        Schema::table('cmapi_cards', function (Blueprint $table) {
            $table->dropIndex(['game']);
            $table->dropColumn('game');
        });
    }
};
