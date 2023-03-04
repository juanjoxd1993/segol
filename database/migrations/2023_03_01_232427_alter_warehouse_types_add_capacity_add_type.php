<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWarehouseTypesAddCapacityAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_types', function (Blueprint $table) {
            $table->double('capacity', 12, 4)->nullable()->default(null);
            $table->integer('type')->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_types', function (Blueprint $table) {
            $table->dropColumn('capacity');
            $table->dropColumn('type');
        });
    }
}
