<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deck_cards', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('deck_id');
            $table->unsignedBigInteger('card_catalog_id');

            $table->integer('quantity')->default(1);

            $table->timestamps();

            // FK
            $table->foreign('deck_id')
                ->references('id')
                ->on('decks')
                ->onDelete('cascade');

            $table->foreign('card_catalog_id')
                ->references('id')
                ->on('card_catalog')
                ->onDelete('cascade');

            // Evita duplicati (stessa carta 2 volte nello stesso deck)
            $table->unique(['deck_id', 'card_catalog_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_cards');
    }
};
