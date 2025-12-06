<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['cvr', 'admin_email', 'address']);
        });
    }

    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('cvr', 255)->nullable();
            $table->string('admin_email', 255)->nullable();
            $table->text('address')->nullable();
        });
    }
};
