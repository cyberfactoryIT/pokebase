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
        Schema::create('deck_evaluation_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_token')->nullable(); // For guest purchases before registration
            $table->foreignId('package_id')->constrained('deck_evaluation_packages')->onDelete('restrict');
            $table->timestamp('purchased_at');
            $table->timestamp('expires_at');
            $table->integer('cards_limit')->nullable(); // Copied from package, null = unlimited
            $table->integer('cards_used')->default(0);
            $table->enum('status', ['active', 'expired', 'consumed'])->default('active');
            $table->string('payment_reference')->nullable(); // Payment provider transaction ID
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['guest_token', 'status']);
            $table->index('expires_at');
            $table->unique('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_evaluation_purchases');
    }
};
