<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organization_id');
            $table->string('number')->unique();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('currency')->default('EUR');
            $table->integer('subtotal_cents');
            $table->integer('tax_cents')->default(0);
            $table->integer('total_cents');
            $table->enum('status', ['draft','open','paid','void','refunded'])->default('open');
            $table->dateTime('issued_at');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('receipt_pdf_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
