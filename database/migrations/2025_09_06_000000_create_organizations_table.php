<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('slug');
            $table->string('cvr')->nullable();
            $table->text('address')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'code']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
