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
        Schema::create('cardmarket_import_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('run_uuid')->unique();
            $table->enum('type', ['catalogue', 'priceguide', 'full', 'products', 'prices'])->index();
            $table->enum('status', ['running', 'success', 'failed'])->default('running')->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->string('source_catalogue_version')->nullable();
            $table->string('source_priceguide_version')->nullable();
            $table->unsignedBigInteger('rows_read')->default(0);
            $table->unsignedBigInteger('rows_upserted')->default(0);
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable(); // Additional metadata (file paths, hashes, etc.)
            $table->timestamps();

            // Index for querying recent runs
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardmarket_import_runs');
    }
};
