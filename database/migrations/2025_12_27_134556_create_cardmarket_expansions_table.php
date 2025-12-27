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
        Schema::create('cardmarket_expansions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cardmarket_expansion_id')->unique();
            $table->string('name');
            $table->string('tcgcsv_group_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('cardmarket_expansion_id');
            $table->index('tcgcsv_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardmarket_expansions');
    }
};
