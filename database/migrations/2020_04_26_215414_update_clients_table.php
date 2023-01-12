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
        Schema::table('clients', function (Blueprint $table) {
			$table->text('contact_name_1')->after('document_number')->nullable();
			$table->text('contact_name_2')->after('contact_name_1')->nullable();
			$table->text('phone_number_3')->after('phone_number_2')->nullable();
			$table->bigInteger('business_unit_id')->after('perception_percentage_id')->unsigned()->nullable();

			$table->foreign('business_unit_id')->references('id')->on('business_units');
		});

		Schema::table('client_addresses', function (Blueprint $table) {
			$table->string('gps_x')->after('ubigeo_id')->nullable();
			$table->string('gps_y')->after('gps_x')->nullable();
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
