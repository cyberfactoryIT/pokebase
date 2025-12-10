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
        Schema::create('pokemon_sets', function (Blueprint $table) {
            $table->id();
            $table->string('set_id', 50)->unique(); // ID del set (es: base1, sv1)
            $table->string('name');
            $table->string('series')->nullable();
            $table->integer('printed_total')->nullable();
            $table->integer('total')->nullable();
            $table->string('ptcgo_code', 20)->nullable();
            $table->date('release_date')->nullable();
            $table->timestamp('api_updated_at')->nullable();
            $table->string('symbol_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('legalities')->nullable(); // unlimited, standard, expanded
            
            // Campi per tracking import
            $table->timestamp('last_import_at')->nullable();
            $table->string('last_import_batch_id', 50)->nullable();
            $table->enum('last_import_status', ['success', 'failed', 'in_progress', 'never'])->default('never');
            $table->integer('cards_imported')->default(0);
            $table->text('last_import_error')->nullable();
            
            $table->timestamps();
            
            $table->index('set_id');
            $table->index('series');
            $table->index('release_date');
            $table->index('last_import_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemon_sets');
    }
};
