<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSalesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
			$table->bigInteger('client_code')->after('client_id')->nullalbe();
			$table->decimal('sale_value', 12, 4)->after('license_plate')->nullable();
            $table->decimal('exonerated_value', 12, 4)->after('sale_value')->nullable();
            $table->decimal('inaccurate_value', 12, 4)->after('exonerated_value')->nullable();
		});

		Schema::table('sale_details', function (Blueprint $table) {
			$table->dropColumn('igv_perception');
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
