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
        Schema::create('internal_batches', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('batch_fk');
            $table->bigInteger('transaction_count');
            $table->string('status');
            $table->string('tid');
            $table->string('mid');
            $table->string('merchant_name');
            $table->string('processor');
            $table->string('batch_running_no');
            $table->bigInteger('merchant_id');
            $table->bigInteger('mid_ppn');
            $table->string('transaction_amount');
            $table->bigInteger('settlement_audit_id');
            $table->string('tax_payment')->nullable();
            $table->string('fee_mdr_merchant')->nullable();
            $table->string('fee_bank_merchant')->nullable();
            $table->string('bank_transfer')->nullable();
            $table->string('created_by');
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
        Schema::dropIfExists('internal_batches');
    }
};
