<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('correlative')->nullable();
            $table->bigInteger('warehouse_movement_id')->unsigned()->nullable();
            $table->bigInteger('client_id')->unsigned()->nullable();
            $table->bigInteger('payment_id')->unsigned()->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->bigInteger('warehouse_document_type_id')->unsigned()->nullable();
            $table->string('referral_serie_number')->nullable();
            $table->string('referral_voucher_number')->nullable();
            $table->string('scop_number')->nullable();
            $table->string('license_plate')->nullable();
            $table->decimal('igv', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->decimal('total_perception', 12, 2)->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('warehouse_movement_id')->references('id')->on('warehouse_movements');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('warehouse_document_type_id')->references('id')->on('warehouse_document_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
