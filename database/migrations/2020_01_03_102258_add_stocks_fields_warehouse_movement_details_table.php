<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStocksFieldsWarehouseMovementDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_movement_details', function(Blueprint $table) {
            $table->decimal('old_stock_good', 12, 4)->after('converted_amount')->nullable();
            $table->decimal('old_stock_repair', 12, 4)->after('old_stock_good')->nullable();
            $table->decimal('old_stock_return', 12, 4)->after('old_stock_repair')->nullable();
            $table->decimal('old_stock_damaged', 12, 4)->after('old_stock_return')->nullable();
            $table->decimal('new_stock_good', 12, 4)->after('old_stock_damaged')->nullable();
            $table->decimal('new_stock_repair', 12, 4)->after('new_stock_good')->nullable();
            $table->decimal('new_stock_return', 12, 4)->after('new_stock_repair')->nullable();
            $table->decimal('new_stock_damaged', 12, 4)->after('new_stock_return')->nullable();
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
