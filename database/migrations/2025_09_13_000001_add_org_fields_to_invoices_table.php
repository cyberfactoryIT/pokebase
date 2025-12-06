<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('org_name')->nullable();
            $table->string('org_company')->nullable();
            $table->string('org_billing_email')->nullable();
            $table->string('org_vat')->nullable();
            $table->string('org_address')->nullable();
            $table->string('org_city')->nullable();
            $table->string('org_country')->nullable();
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'org_name',
                'org_company',
                'org_billing_email',
                'org_vat',
                'org_address',
                'org_city',
                'org_country',
            ]);
        });
    }
};
