<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->enum('type', ['percent','fixed'])->default('percent');
            $table->integer('value');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('max_redemptions')->nullable();
            $table->integer('per_org_limit')->nullable();
            $table->boolean('new_orgs_only')->default(false);
            $table->boolean('stackable')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
