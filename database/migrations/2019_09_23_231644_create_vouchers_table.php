<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Company
            $table->bigInteger('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies');

            // Client
            $table->bigInteger('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->bigInteger('original_client_id')->unsigned();
            $table->foreign('original_client_id')->references('id')->on('clients');
            $table->string('client_name')->nullable();
            $table->string('client_address')->nullable();

            // Voucher
            $table->bigInteger('voucher_type_id')->unsigned();
            $table->foreign('voucher_type_id')->references('id')->on('voucher_types');
            $table->string('serie_number')->nullable();
            $table->bigInteger('voucher_number')->nullable();
            $table->bigInteger('order_series')->nullable();
            $table->bigInteger('order_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->time('issue_hour')->nullable();
            $table->date('expiry_date')->nullable();
            $table->bigInteger('currency_id')->unsigned();
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->bigInteger('payment_id')->unsigned();
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->decimal('taxed_operation', 12, 2)->nullable();
            $table->decimal('unaffected_operation', 12, 2)->nullable();
            $table->decimal('exonerated_operation', 12, 2)->nullable();
            $table->decimal('igv', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->decimal('igv_perception', 12, 2)->nullable();
            $table->decimal('total_perception', 12, 2)->nullable();
            $table->decimal('igv_percentage', 5, 2)->nullable();
            $table->decimal('igv_perception_percentage', 5, 2)->nullable();
            $table->bigInteger('ose')->default(0);
            $table->timestamps();
            $table->string('user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
