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
        Schema::create('deck_evaluation_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // EVAL_100, EVAL_600, EVAL_UNLIMITED
            $table->string('name');
            $table->integer('max_cards')->nullable(); // null = unlimited
            $table->integer('validity_days'); // 30, 30, 365
            $table->boolean('allows_multiple_decks')->default(false);
            $table->integer('price_cents'); // One-shot price in cents
            $table->string('currency', 3)->default('EUR');
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_evaluation_packages');
    }
};
