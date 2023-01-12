<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function(Blueprint $table) {
            $table->bigInteger('zone_id')->after('perception_percentage_id')->unsigned()->nullable();
            $table->bigInteger('channel_id')->after('zone_id')->unsigned()->nullable();
            $table->bigInteger('route_id')->after('channel_id')->unsigned()->nullable();
            $table->bigInteger('sector_id')->after('route_id')->unsigned()->nullable();

            $table->dropColumn(['sales_area', 'sales_group']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function(Blueprint $table) {
            $table->string('sales_area');
            $table->string('sales_group');
        });
    }
}
