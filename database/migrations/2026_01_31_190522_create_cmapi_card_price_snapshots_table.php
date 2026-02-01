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
            $table->foreignId('cmapi_card_id')->constrained('cmapi_cards')->onDelete('cascade');
            $table->decimal('price_eur', 10, 2)->nullable();
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->string('language', 10)->nullable()->comment('en, fr, de, it, es, etc.');
            $table->string('condition', 20)->nullable()->comment('NM, LP, MP, HP, DMG');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['cmapi_card_id', 'recorded_at'], 'idx_card_recorded');
            $table->index(['cmapi_card_id', 'language', 'recorded_at'], 'idx_card_lang_recorded');
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
