<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiquidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liquidations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sale_id')->unsigned()->nullable();
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('payment_method_id')->unsigned()->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->decimal('exchange_rate', 12, 4)->nullable();
            $table->bigInteger('bank_id')->unsigned()->nullable();
            $table->string('operation_number')->nullable();
            $table->decimal('amount', 12, 4)->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('bank_id')->references('id')->on('banks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liquidations');
    }
}