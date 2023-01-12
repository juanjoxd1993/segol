<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sale_id')->unsigned()->nullable();
            $table->bigInteger('item_number')->nullable();
            $table->bigInteger('article_id')->unsigned()->nullable();
            $table->decimal('price_igv', 12, 4)->nullable();
            $table->decimal('sale_value', 12, 4)->nullable();
            $table->decimal('inaccurate_value', 12, 4)->nullable();
            $table->decimal('exonerated_value', 12, 4)->nullable();
            $table->decimal('igv', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->decimal('igv_perception', 12, 2)->nullable();
            $table->decimal('total_perception', 12, 2)->nullable();
            $table->decimal('igv_percentage', 5, 2)->nullable();
            $table->decimal('igv_perception_percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('article_id')->references('id')->on('articles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_details');
    }
}
