<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSalesLiquidationsClientsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
			$table->date('expiry_date')->after('sale_date')->nullable();
		});

		Schema::table('liquidations', function (Blueprint $table) {
			$table->bigInteger('collection_number')->after('sale_id')->nullable();
		});

		Schema::table('clients', function (Blueprint $table) {
			$table->decimal('credit_balance', 12, 2)->after('credit_limit_days')->nullable();
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
