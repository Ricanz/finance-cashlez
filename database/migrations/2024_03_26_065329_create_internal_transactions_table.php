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
        Schema::create('internal_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('settlement_date')->nullable();
            $table->string('retrieval_number')->nullable();
            $table->string('transaction_amount')->nullable();
            $table->string('bank_payment')->nullable();
            $table->string('txid')->nullable();
            $table->string('batch_fk')->nullable();
            $table->string('bank_fee_amount')->nullable();
            $table->string('merchant_fee_amount')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('status')->nullable();
            $table->string('comparator_code')->nullable();
            $table->string('autn_code')->nullable();
            $table->string('sid')->nullable();
            $table->string('ftp_file')->nullable();
            $table->string('number_va')->nullable();
            $table->integer('bank_id')->nullable();
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
        Schema::dropIfExists('internal_transactions');
    }
};
