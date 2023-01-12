<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
			$table->string('guide_series')->after('currency_id')->nullable();
			$table->string('guide_number')->after('guide_series')->nullable();
			$table->string('order_series')->after('guide_number')->nullable();
			$table->string('order_number')->after('order_series')->nullable();
			$table->date('sale_date')->after('company_id')->nullable();
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
