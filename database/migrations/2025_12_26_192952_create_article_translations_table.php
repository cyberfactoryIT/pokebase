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
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5); // en, it, da
            $table->string('title');
            $table->text('excerpt');
            $table->longText('body');
            $table->boolean('is_auto_translated')->default(true);
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();

            // Unique constraint: one translation per article per locale
            $table->unique(['article_id', 'locale']);
            $table->index(['article_id', 'locale']);
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_translations');
    }
};
