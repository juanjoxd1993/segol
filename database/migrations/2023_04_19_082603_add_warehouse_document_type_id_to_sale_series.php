<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWarehouseDocumentTypeIdToSaleSeries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_series', function (Blueprint $table) {
            $table->bigInteger('warehouse_document_type_id')->unsigned()->nullable();
            $table
                ->foreign('warehouse_document_type_id')
                ->references('id')
                ->on('warehouse_document_types')
                ->after('num_serie');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_series', function (Blueprint $table) {
            //
        });
    }
}
