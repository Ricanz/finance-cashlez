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
        Schema::create('upload_bank_details', function (Blueprint $table) {
            $table->id();
            $table->string('token_applicant', 150)->nullable();
            $table->string('account_no', 150)->nullable();
            $table->string('mid', 150)->nullable();
            $table->string('merchant_name', 150)->nullable();
            $table->string('amount_debit', 150)->nullable();
            $table->string('amount_credit', 150)->nullable();
            $table->string('transfer_date', 150)->nullable();
            $table->string('date', 150)->nullable();
            $table->string('statement_code', 150)->nullable();
            $table->string('type_code', 4)->nullable();
            $table->mediumText('description1')->nullable();
            $table->mediumText('description2')->nullable();
            $table->string('created_by', 150)->nullable();
            $table->string('modified_by', 150)->nullable();
            $table->boolean('modified_by')->default(false);
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
        Schema::dropIfExists('upload_bank_details');
    }
};
