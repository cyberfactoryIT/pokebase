<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcgdx_sets', function (Blueprint $table) {
            $table->id();
            $table->string('tcgdex_id')->unique();
            $table->json('name');
            $table->string('series')->nullable();
            $table->text('logo_url')->nullable();
            $table->text('symbol_url')->nullable();
            $table->date('release_date')->nullable();
            $table->unsignedInteger('card_count_total')->nullable();
            $table->unsignedInteger('card_count_official')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            
            $table->index('tcgdex_id');
            $table->index('release_date');
            $table->index('series');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcgdx_sets');
    }
};
