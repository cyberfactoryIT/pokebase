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
        Schema::create('tcgcsv_products', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id')->default(3)->index();
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('product_id')->unique();
            $table->string('name')->nullable();
            $table->string('clean_name')->nullable();
            $table->text('image_url')->nullable();
            $table->string('rarity')->nullable();
            $table->string('card_number')->nullable();
            $table->dateTime('modified_on')->nullable();
            $table->json('extended_data')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            
            $table->index(['category_id', 'group_id']);
            $table->index('card_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcgcsv_products');
    }
};
