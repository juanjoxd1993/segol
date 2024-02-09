<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMovementStockTypesFieldWarehouseMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_movements', function(Blueprint $table) {
            $table->bigInteger('movement_stock_type_id')->after('movement_type_id')->unsigned()->nullable();
            $table->foreign('movement_stock_type_id')->references('id')->on('movement_stock_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
