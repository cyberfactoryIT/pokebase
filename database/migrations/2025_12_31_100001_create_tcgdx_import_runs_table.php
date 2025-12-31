<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tcgdx_import_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', ['running', 'success', 'failed'])->default('running');
            $table->string('scope')->nullable();
            $table->json('stats')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tcgdx_import_runs');
    }
};
