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
        Schema::create('upload_banks', function (Blueprint $table) {
            $table->id();
            $table->string('token_applicant', 150)->nullable();
            $table->string('type', 150)->nullable();
            $table->string('url', 150)->nullable();
            $table->string('processor', 150)->nullable();
            $table->string('process_status', 150)->nullable();
            $table->string('start_recon_by', 150)->nullable();
            $table->boolean('is_reconcile')->default(false);
            $table->string('created_by', 150)->nullable();
            $table->string('updated_by', 150)->nullable();
            $table->string('file_id', 255)->nullable();
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
        Schema::dropIfExists('upload_banks');
    }
};
