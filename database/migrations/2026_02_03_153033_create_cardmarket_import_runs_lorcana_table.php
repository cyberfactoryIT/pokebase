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
        // Import runs tracking for Lorcana
        Schema::create('cardmarket_import_runs_lorcana', function (Blueprint $table) {
            $table->id();
            $table->uuid('run_uuid')->unique();
            $table->string('type')->default('full'); // full, products, prices
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->integer('rows_read')->default(0);
            $table->integer('rows_upserted')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardmarket_import_runs_lorcana');
    }
};
