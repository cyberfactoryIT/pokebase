<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('value_type', ['bool','int','string'])->default('bool');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('features');
    }
}
