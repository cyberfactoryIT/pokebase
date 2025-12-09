<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_catalog', function (Blueprint $table) {
            $table->id();

            // Multi-universo: ogni carta appartiene a un game (Pokémon, Magic, ecc.)
            $table->unsignedBigInteger('game_id');

            // Dati base della carta (generici, vanno bene per Pokémon e in futuro altri giochi)
            $table->string('name');                 // Nome carta
            $table->string('set_name')->nullable(); // Nome espansione
            $table->string('set_code')->nullable(); // Codice espansione
            $table->string('collector_number')->nullable(); // Numero carta nel set
            $table->string('rarity')->nullable();   // Common, Rare, ecc.
            $table->string('type_line')->nullable(); // Tipo (creatura, trainer, ecc.)
            $table->string('image_url')->nullable(); // URL immagine ufficiale o proxy

            // Per eventuali dati specifici che non vuoi ancora strutturare
            $table->json('extra_data')->nullable();

            $table->timestamps();

            // FK verso games
            $table->foreign('game_id')
                ->references('id')
                ->on('games');

            // Indici utili per la search
            $table->index(['game_id', 'name']);
            $table->index(['game_id', 'set_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_catalog');
    }
};
