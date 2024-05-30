<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reconcile_results', function (Blueprint $table) {
            $table->id();
            $table->string('token_applicant');
            $table->bigInteger('statement_id');
            $table->bigInteger('request_id');
            $table->string('status');
            $table->string('tid')->nullable();
            $table->string('mid');
            $table->string('batch_fk')->nullable();
            $table->string('trx_counts');
            $table->string('total_sales');
            $table->string('processor_payment');
            $table->string('internal_payment');
            $table->string('merchant_payment');
            $table->string('merchant_name');
            $table->bigInteger('merchant_id');
            $table->string('transfer_amount');
            $table->string('bank_settlement_amount');
            $table->string('tax_payment');
            $table->string('fee_mdr_merchant');
            $table->string('fee_bank_merchant');
            $table->string('bank_transfer');
            $table->string('created_by');
            $table->string('modified_by');
            $table->timestamp('settlement_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reconcile_results');
    }
};
