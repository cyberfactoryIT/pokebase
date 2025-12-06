<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('responsible_user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name', 191);
            $table->string('code', 64);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('billable')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['organization_id', 'code'], 'projects_org_code_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
