<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('voucher_id')->unsigned();
            $table->foreign('voucher_id')->references('id')->on('vouchers');
            $table->bigInteger('unit_id')->unsigned();
            $table->foreign('unit_id')->references('id')->on('units');
            $table->string('name')->nullable();
            $table->decimal('quantity', 12, 4)->nullable();
            $table->decimal('original_price', 12, 4)->nullable();
            $table->decimal('unit_price', 12, 4)->nullable();
            $table->decimal('sale_value', 12, 4)->nullable();
            $table->decimal('exonerated_value', 12, 4)->nullable();
            $table->decimal('inaccurate_value', 12, 4)->nullable();
            $table->decimal('igv', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
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
        Schema::dropIfExists('voucher_details');
    }
}
