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
        Schema::table('tcgcsv_import_logs', function (Blueprint $table) {
            $table->string('type')->default('import')->after('batch_id'); // 'import' or 'checkup'
            $table->string('run_id')->nullable()->after('type'); // UUID for checkups
            $table->string('message')->nullable()->after('status');
            $table->integer('duration_ms')->nullable()->after('completed_at');
            $table->json('metrics')->nullable()->after('options');
            
            $table->index('type');
            $table->index('run_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_import_logs', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['run_id']);
            $table->dropColumn(['type', 'run_id', 'message', 'duration_ms', 'metrics']);
        });
    }
};
