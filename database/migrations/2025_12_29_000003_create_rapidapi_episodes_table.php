<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapidapi_episodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('episode_id')->unique()->index();
            $table->string('game', 50)->index();
            $table->string('name')->index();
            $table->string('slug')->index();
            $table->string('code', 10)->nullable();
            $table->date('released_at')->index();
            $table->text('logo_url')->nullable();
            $table->unsignedInteger('cards_total')->default(0);
            $table->unsignedInteger('cards_printed_total')->default(0);
            $table->unsignedInteger('series_id')->nullable();
            $table->string('series_name')->nullable();
            $table->decimal('cardmarket_total_value', 10, 2)->default(0)->index();
            $table->decimal('tcgplayer_total_value', 10, 2)->default(0);
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index(['game', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapidapi_episodes');
    }
};
