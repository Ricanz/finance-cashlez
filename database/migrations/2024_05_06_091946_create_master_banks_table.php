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
        Schema::create('master_banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('remark')->nullable();
            $table->bigInteger('version')->nullable();
            $table->string('created_by')->nullable();
            $table->dateTime('created_date')->nullable();
            $table->string('modified_by')->nullable();
            $table->dateTime('modified_date')->nullable();
            $table->dateTime('activate_date')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('suspend_date')->nullable();
            $table->dateTime('terminate_date')->nullable();
            $table->string('business_address1')->nullable();
            $table->string('business_address2')->nullable();
            $table->string('business_contact')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('state')->nullable();
            $table->string('bank_name')->nullable();
            $table->bigInteger('setting_fk')->nullable();
            $table->string('bank_reference', 15)->nullable();
            $table->string('receipt_footer_message')->nullable();
            $table->char('virtual_mid_tid', 1)->default('0');
            $table->string('bank_status', 10)->default('UP');
            $table->string('cashlez_account')->nullable();
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
        Schema::dropIfExists('master_banks');
    }
};
