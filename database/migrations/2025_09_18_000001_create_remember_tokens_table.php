<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('remember_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('selector', 32)->unique();
            $table->string('token_hash', 64);
            $table->string('user_agent', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('remember_tokens');
    }
};
