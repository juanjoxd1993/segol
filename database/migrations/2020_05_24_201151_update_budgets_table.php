<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budgets', function (Blueprint $table) {
			$table->renameColumn('group_id', 'family_id');
			$table->bigInteger('sale_option_id')->after('percentage')->unsigned()->nullable();

			$table->foreign('family_id')->references('id')->on('classifications');
			$table->foreign('sale_option_id')->references('id')->on('sale_options');
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
