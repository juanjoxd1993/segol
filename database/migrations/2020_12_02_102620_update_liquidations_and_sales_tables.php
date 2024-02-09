<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateLiquidationsAndSalesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('liquidations', function (Blueprint $table) {
			$table->dropForeign('liquidations_bank_id_foreign');
			$table->renameColumn('bank_id', 'bank_account_id');
			$table->foreign('bank_account_id')->references('id')->on('bank_accounts');
			$table->string('detraction_number')->after('operation_number')->nullable();
			$table->integer('collection')->default(0)->after('amount')->nullable();
		});

		Schema::table('sales', function (Blueprint $table) {
			$table->decimal('balance', 12, 2)->after('total_perception')->nullable();
			$table->decimal('paid', 12, 2)->after('balance')->default(0)->nullable();
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
