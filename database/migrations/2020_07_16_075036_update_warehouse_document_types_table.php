<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWarehouseDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_document_types', function (Blueprint $table) {
			$table->bigInteger('voucher_type_id')->after('short_name')->unsigned()->nullable();
			$table->bigInteger('previous_date_flag')->after('voucher_type_id')->nullable();
			$table->bigInteger('same_voucher_number_flag')->after('previous_date_flag')->nullable();

			$table->foreign('voucher_type_id')->references('id')->on('voucher_types');
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
