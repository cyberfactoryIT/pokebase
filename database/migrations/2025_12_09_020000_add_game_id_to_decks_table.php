<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decks', function (Blueprint $table) {
            $table->id();

            // Proprietario del deck
            $table->unsignedBigInteger('user_id');

            // Universo di gioco (Pokémon, Magic, ecc.)
            $table->unsignedBigInteger('game_id');

            // Dati base del deck
            $table->string('name');             // Nome del mazzo
            $table->string('format')->nullable();   // es. "standard", "modern", ecc. (per il futuro)
            $table->text('description')->nullable();

            $table->timestamps();

            // FK verso users e games
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('game_id')
                ->references('id')
                ->on('games');

            // Indici utili
            $table->index(['user_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decks');
    }
};

