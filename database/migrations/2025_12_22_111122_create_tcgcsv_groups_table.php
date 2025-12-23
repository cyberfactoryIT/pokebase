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
        Schema::create('tcgcsv_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id')->default(3)->index();
            $table->unsignedBigInteger('group_id')->unique();
            $table->string('name')->nullable();
            $table->string('abbreviation')->nullable();
            $table->dateTime('published_on')->nullable();
            $table->dateTime('modified_on')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            
            $table->index(['category_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcgcsv_groups');
    }
};
