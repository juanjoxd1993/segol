<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StocksWarehouse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_movements', function (Blueprint $table) {
            $table->double('stock_ini', 10, 2)->nullable()->default(null);
            $table->double('stock_out', 10, 2)->nullable()->default(null);
            $table->double('stock_sale', 10, 2)->nullable()->default(null);
            $table->double('stock_pend', 10, 2)->nullable()->default(null);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_movements', function (Blueprint $table) {
            $table->dropColumn('stock_ini');
            $table->dropColumn('stock_out');
            $table->dropColumn('stock_sale');
            $table->dropColumn('stock_pend');
        });
    }
}
