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
            $table->timestamp('settlement_date');
            $table->string('retrieval_number');
            $table->string('transaction_amount');
            $table->string('bank_payment');
            $table->string('txid');
            $table->string('batch_fk');
            $table->string('bank_fee_amount');
            $table->string('merchant_fee_amount');
            $table->string('tax_amount');
            $table->string('transaction_type');
            $table->string('status');
            $table->string('comparator_code');
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
