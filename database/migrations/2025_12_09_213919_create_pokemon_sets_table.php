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
            $table->string('set_id', 50)->unique(); // es: base1, sv1
            $table->string('name');
            $table->string('series')->nullable();
            $table->integer('printed_total')->nullable();
            $table->integer('total')->nullable();
            $table->json('legalities')->nullable();
            $table->string('ptcgo_code', 20)->nullable();
            $table->date('release_date')->nullable();
            $table->timestamp('api_updated_at')->nullable();
            $table->json('images')->nullable(); // symbol e logo URLs
            
            // Campi per tracking import
            $table->timestamp('last_import_at')->nullable();
            $table->enum('last_import_status', ['pending', 'success', 'failed', 'partial'])->default('pending');
            $table->integer('cards_imported')->default(0);
            $table->text('last_import_error')->nullable();
            
            $table->timestamps();
            
            $table->index('set_id');
            $table->index('series');
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
