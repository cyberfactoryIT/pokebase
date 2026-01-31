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
        // Sets table (called "episodes" in CMAPI)
        Schema::create('cmapi_sets', function (Blueprint $table) {
            $table->id();
            $table->string('cmapi_id')->unique()->index(); // API episode/set ID
            $table->string('name');
            $table->string('code')->nullable()->index(); // Set code
            $table->string('logo_url')->nullable();
            $table->date('release_date')->nullable();
            $table->integer('card_count')->nullable();
            $table->json('raw')->nullable(); // Full API response
            $table->timestamps();
        });

        // Cards table
        Schema::create('cmapi_cards', function (Blueprint $table) {
            $table->id();
            $table->string('cmapi_id')->unique()->index(); // API card ID
            $table->unsignedBigInteger('set_cmapi_id')->index();
            $table->string('name');
            $table->string('number')->nullable();
            $table->string('rarity')->nullable();
            $table->string('image_small_url')->nullable();
            $table->string('image_large_url')->nullable();
            
            // Pricing (in cents from API, stored as decimal)
            $table->decimal('price_eur', 10, 2)->nullable()->index();
            $table->decimal('price_usd', 10, 2)->nullable()->index();
            
            // Lorcana-specific fields
            $table->integer('ink_cost')->nullable();
            $table->string('card_type')->nullable(); // Character, Action, Item, Location
            $table->integer('lore_value')->nullable();
            $table->string('ink_color')->nullable(); // Amber, Amethyst, Emerald, Ruby, Sapphire, Steel
            
            // One Piece-specific fields (nullable, will be used when importing One Piece)
            $table->integer('cost')->nullable();
            $table->integer('power')->nullable();
            $table->integer('counter')->nullable();
            $table->string('color')->nullable();
            
            $table->json('raw')->nullable(); // Full API response
            $table->timestamps();
            
            $table->foreign('set_cmapi_id')
                  ->references('id')
                  ->on('cmapi_sets')
                  ->onDelete('cascade');
        });

        // Import runs tracking
        Schema::create('cmapi_import_runs', function (Blueprint $table) {
            $table->id();
            $table->string('game')->default('lorcana'); // 'lorcana', 'onepiece', etc.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('status')->default('running'); // running, success, failed
            $table->string('scope')->default('all'); // all, single_set
            $table->json('stats')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cmapi_cards');
        Schema::dropIfExists('cmapi_sets');
        Schema::dropIfExists('cmapi_import_runs');
    }
};
