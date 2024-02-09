<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseMovementDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_movement_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('warehouse_movement_id')->unsigned()->nullable();
            $table->bigInteger('item_number')->nullable();
            $table->bigInteger('article_code')->unsigned()->nullable();
            $table->decimal('digit_amount', 12, 0)->nullable();
            $table->decimal('converted_amount', 12, 0)->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->decimal('price', 12, 4)->nullable();
            $table->decimal('sale_value', 12, 4)->nullable();
            $table->decimal('exonerated_value', 12, 4)->nullable();
            $table->decimal('inaccurate_value', 12, 4)->nullable();
            $table->decimal('igv', 12, 4)->nullable();
            $table->decimal('total', 12, 4)->nullable();
            $table->decimal('igv_perception', 12, 4)->nullable();
            $table->decimal('igv_percentage', 5, 2)->nullable();
            $table->decimal('igv_perception_percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('warehouse_movement_id')->references('id')->on('warehouse_movements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_movement_details');
    }
}
