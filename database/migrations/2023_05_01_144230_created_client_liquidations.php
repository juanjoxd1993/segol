<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatedClientLiquidations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_liquidations', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('warehouse_movement_id');
            $table->bigInteger('client_id');
            $table->bigInteger('article_id');
            $table->bigInteger('quantity');
            $table->timestamps();
            $table->softDeletes();
            /*$table->foreign('warehouse_movement_id')
                ->references('id')
                ->on('warehouse_movements');
            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
            $table->foreign('article_id')
                ->references('id')
                ->on('articles');*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_liquidations');
    }
}
