<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcgdx_cards', function (Blueprint $table) {
            $table->id();
            $table->string('tcgdex_id')->unique();
            $table->foreignId('set_tcgdx_id')->constrained('tcgdx_sets')->onDelete('cascade');
            $table->string('local_id')->nullable();
            $table->string('number')->nullable();
            $table->json('name');
            $table->string('rarity')->nullable();
            $table->string('illustrator')->nullable();
            $table->text('image_small_url')->nullable();
            $table->text('image_large_url')->nullable();
            $table->json('types')->nullable();
            $table->json('subtypes')->nullable();
            $table->string('supertype')->nullable();
            $table->unsignedInteger('hp')->nullable();
            $table->string('evolves_from')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            
            $table->index('tcgdex_id');
            $table->index(['set_tcgdx_id', 'local_id']);
            $table->index('rarity');
            $table->index('supertype');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcgdx_cards');
    }
};
