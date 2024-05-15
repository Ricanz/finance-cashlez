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
            $table->string('report_partner')->nullable();
            $table->string('bo_detail_transaction')->nullable();
            $table->string('bo_summary')->nullable();
            $table->string('bank_statement')->nullable();
            $table->string('created_by')->nullable();
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
