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
        Schema::create('deck_evaluation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_token')->nullable()->unique(); // Secure token for guest access
            $table->foreignId('guest_deck_id')->nullable()->constrained('guest_decks')->onDelete('set null');
            $table->foreignId('deck_valuation_id')->nullable()->constrained('deck_valuations')->onDelete('set null');
            $table->enum('status', ['draft', 'processed', 'expired'])->default('draft');
            $table->integer('free_cards_limit')->default(10);
            $table->integer('free_cards_used')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['guest_token', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_evaluation_sessions');
    }
};
