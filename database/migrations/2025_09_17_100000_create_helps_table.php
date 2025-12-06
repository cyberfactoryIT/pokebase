<?php
// database/migrations/2025_09_17_100000_create_helps_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('helps', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128)->unique();        // es. security.2fa
            $table->string('icon', 64)->nullable();      // es. shield-check
            $table->json('title')->nullable();           // {en,it,da}
            $table->json('short')->nullable();           // {en,it,da}
            $table->json('long')->nullable();            // {en,it,da} (markdown)
            $table->json('links')->nullable();           // [{route,label:{en,it,da}}]
            $table->json('meta')->nullable();            // libero (ordinamento, categorie, tags)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('helps');
    }
};
