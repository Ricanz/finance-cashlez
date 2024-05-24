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
        Schema::create('report_partners', function (Blueprint $table) {
            $table->id();
            $table->string('token_applicant')->nullable();
            $table->string('date')->nullable();
            $table->string('description')->nullable();
            $table->string('ftp_file')->nullable();
            $table->string('number_va')->nullable();
            $table->string('auth_code')->nullable();
            $table->string('sid')->nullable();
            $table->string('rrn')->nullable();
            $table->string('net_amount')->nullable();
            $table->string('channel')->nullable();
            $table->boolean('is_reconcile')->default(0);
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
        Schema::dropIfExists('report_partners');
    }
};
