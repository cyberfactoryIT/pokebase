<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrganizationsAddPlanAndBilling extends Migration
{
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('pricing_plan_id')->nullable()->after('id');
            $table->string('billing_email')->nullable()->after('pricing_plan_id');
            $table->string('company')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();

            $table->index('pricing_plan_id', 'org_plan_idx');
            $table->index('billing_email', 'org_billing_email_idx');
            $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onUpdate('cascade')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['pricing_plan_id']);
            $table->dropIndex('org_plan_idx');
            $table->dropIndex('org_billing_email_idx');
            $table->dropColumn([
                'pricing_plan_id','billing_email','company','vat_number','address_line1','address_line2','city','postcode','country'
            ]);
        });
    }
}
