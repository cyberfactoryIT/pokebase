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
        Schema::create('cmapi_card_price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cmapi_card_id');
            $table->string('condition', 10); // NM, EX, etc.
            $table->string('language', 5)->nullable(); // en, fr, de, es, it
            $table->decimal('price_eur', 10, 2)->nullable();
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index('cmapi_card_id');
            $table->index('recorded_at');
            $table->index(['cmapi_card_id', 'condition', 'language'], 'cmapi_snapshots_card_cond_lang_idx');
            
            // Foreign key
            $table->foreign('cmapi_card_id')->references('id')->on('cmapi_cards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cmapi_card_price_snapshots');
    }
};
