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
        Schema::create('bank_statement_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('file_name');
            $table->string('processor');
            $table->string('process_status');
            $table->string('start_recon_by');
            $table->string('created_by');
            $table->string('updated_at');
            $table->string('file_id');
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
        Schema::dropIfExists('bank_statement_requests');
    }
};
