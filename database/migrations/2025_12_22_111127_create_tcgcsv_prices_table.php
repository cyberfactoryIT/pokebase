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
        Schema::create('tcgcsv_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id')->default(3)->index();
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('printing')->nullable();
            $table->string('condition')->nullable();
            $table->decimal('market_price', 10, 2)->nullable();
            $table->decimal('low_price', 10, 2)->nullable();
            $table->decimal('mid_price', 10, 2)->nullable();
            $table->decimal('high_price', 10, 2)->nullable();
            $table->decimal('direct_low_price', 10, 2)->nullable();
            $table->dateTime('snapshot_at')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            
            $table->index(['category_id', 'group_id']);
            $table->index(['product_id', 'snapshot_at']);
            $table->unique(['product_id', 'printing', 'condition', 'snapshot_at'], 'tcgcsv_prices_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcgcsv_prices');
    }
};
