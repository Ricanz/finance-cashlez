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
        Schema::create('bank_statement_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_id');
            $table->string('amount_c  redit');
            $table->string('mid');
            $table->string('merchant_name');
            $table->string('description1')->nullable();
            $table->string('description2')->nullable();
            $table->string('amount_debit');
            $table->string('statement_code');
            $table->boolean('is_reconcile')->default(false);
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
        Schema::dropIfExists('bank_statement_details');
    }
};
