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
        Schema::create('bank_parameters', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('channel_id');
            $table->boolean('report_partner');
            $table->boolean('bo_detail_transaction');
            $table->boolean('bo_summary');
            $table->boolean('bank_statement');
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
        Schema::dropIfExists('bank_parameters');
    }
};
