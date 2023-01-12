<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('year')->nullable();
			$table->string('month')->nullable();
			$table->bigInteger('business_unit_id')->unsigned()->nullable();
			$table->decimal('price', 14, 4)->nullable();
            $table->timestamps();
			$table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

			$table->foreign('business_unit_id')->references('id')->on('business_units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_histories');
    }
}
