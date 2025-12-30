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
        Schema::table('decks', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('description');
            $table->string('shared_token', 64)->nullable()->unique()->after('is_shared');
            $table->timestamp('shared_at')->nullable()->after('shared_token');
            
            $table->index('is_shared');
            $table->index('shared_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decks', function (Blueprint $table) {
            $table->dropIndex(['is_shared']);
            $table->dropIndex(['shared_token']);
            $table->dropColumn(['is_shared', 'shared_token', 'shared_at']);
        });
    }
};
