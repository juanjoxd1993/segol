<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSaleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_details', function (Blueprint $table) {
			$table->longText('concept')->after('article_id')->nullable();
			$table->bigInteger('unit_id')->after('concept')->unsigned()->nullable();
			$table->bigInteger('referential_unit_id')->after('unit_id')->unsigned()->nullable();
			$table->bigInteger('value_type_id')->after('referential_unit_id')->unsigned()->nullable();
			$table->string('referral_guide_series')->after('value_type_id')->nullable();
			$table->string('referral_guide_number')->after('referral_guide_series')->nullable();
			$table->string('carrier_series')->after('referral_guide_number')->nullable();
			$table->string('carrier_number')->after('carrier_series')->nullable();
			$table->string('license_plate')->after('carrier_number')->nullable();
			$table->decimal('referential_quantity', 14, 6)->after('quantity')->nullable();
			$table->decimal('referential_sale_value', 14, 6)->after('sale_value')->nullable();
			$table->decimal('referential_convertion', 12, 2)->after('igv_perception_percentage')->nullable();
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