<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('year')->nullable();
			$table->string('month')->nullable();
			$table->string('days')->nullable();
			$table->bigInteger('business_unit_id')->unsigned()->nullable();
			$table->bigInteger('client_channel_id')->unsigned()->nullable();
			$table->bigInteger('client_sector_id')->unsigned()->nullable();
			$table->bigInteger('client_zone_id')->unsigned()->nullable();
			$table->bigInteger('group_id')->unsigned()->nullable();
			$table->bigInteger('subgroup_id')->unsigned()->nullable();
			$table->bigInteger('region_id')->unsigned()->nullable();
			$table->decimal('metric_tons', 12, 2)->nullable();
			$table->decimal('total', 12, 2)->nullable();
			$table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();
			$table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
			$table->softDeletes();

			$table->foreign('business_unit_id')->references('id')->on('business_units');
			$table->foreign('client_channel_id')->references('id')->on('client_channels');
			$table->foreign('client_sector_id')->references('id')->on('client_sectors');
			$table->foreign('client_zone_id')->references('id')->on('client_zones');
			$table->foreign('group_id')->references('id')->on('classifications');
			$table->foreign('subgroup_id')->references('id')->on('classifications');
			$table->foreign('region_id')->references('id')->on('regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budgets');
    }
}
