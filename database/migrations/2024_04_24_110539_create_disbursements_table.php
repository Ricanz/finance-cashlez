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
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('is_disbursed')->default(false);
            $table->bigInteger('transaction_count');
            $table->string('total_amount');
            $table->string('bank_code');
            $table->string('transfer_fee');
            $table->string('transfer_net');
            $table->string('revenue');
            $table->string('email_requestor');
            $table->string('created_by');
            $table->string('updated_by');
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
        Schema::dropIfExists('disbursements');
    }
};
