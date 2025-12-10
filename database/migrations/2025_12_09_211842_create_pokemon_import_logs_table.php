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
        Schema::create('pokemon_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id', 50)->unique(); // Identificatore univoco per questo import
            $table->string('set_code')->nullable(); // Se import specifico di un set
            $table->integer('start_page')->default(1);
            $table->integer('current_page')->nullable();
            $table->integer('total_pages')->nullable();
            $table->enum('status', ['started', 'in_progress', 'completed', 'failed', 'cancelled'])->default('started');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('cards_processed')->default(0);
            $table->integer('cards_new')->default(0);
            $table->integer('cards_updated')->default(0);
            $table->integer('cards_failed')->default(0);
            $table->json('failed_cards')->nullable(); // Lista delle carte fallite
            $table->json('pages_completed')->nullable(); // Array delle pagine completate
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('batch_id');
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemon_import_logs');
    }
};
