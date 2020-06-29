<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billable_id');
            $table->string('billable_type');
            $table->string('iyzico_id')->nullable();
            $table->string('name');
            $table->string('surname');
            $table->string('gsm_number');
            $table->string('iyzico_email')->unique();
            $table->string('identity_number');
            $table->string('shipping_contact_name');
            $table->string('shipping_city');
            $table->string('shipping_country');
            $table->string('shipping_address');
            $table->string('shipping_zip_code');
            $table->string('billing_contact_name');
            $table->string('billing_city');
            $table->string('billing_country');
            $table->string('billing_address');
            $table->string('billing_zip_code');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
