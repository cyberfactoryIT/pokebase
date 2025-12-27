<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops all Pokemon TCG API related tables permanently.
     * These tables were deprecated and are no longer used by the application.
     * 
     * The application now uses TCGCSV as the primary card database:
     * - tcgcsv_products (141,675 products across all games)
     * - tcgcsv_groups (652 groups)
     * - user_collection and deck_cards use tcgcsv_products.product_id
     */
    public function up(): void
    {
        // Drop deprecated tables (previously renamed from card_catalog and pokemon_sets)
        Schema::dropIfExists('deprecated_card_catalog');
        Schema::dropIfExists('deprecated_pokemon_sets');
        
        // Drop import logs table (no longer needed)
        Schema::dropIfExists('pokemon_import_logs');
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This rollback will NOT restore data, only recreate empty tables.
     * Data was in the deprecated tables which are now permanently dropped.
     */
    public function down(): void
    {
        // Recreate pokemon_import_logs
        Schema::create('pokemon_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->string('set_code')->nullable();
            $table->integer('start_page')->default(1);
            $table->integer('total_pages')->nullable();
            $table->string('status'); // started, in_progress, completed, failed
            $table->json('pages_completed')->nullable();
            $table->integer('cards_processed')->default(0);
            $table->integer('cards_new')->default(0);
            $table->integer('cards_updated')->default(0);
            $table->integer('cards_failed')->default(0);
            $table->json('failed_cards')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Note: deprecated_card_catalog and deprecated_pokemon_sets 
        // are not recreated in rollback as they were already deprecated
    }
};
