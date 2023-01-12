<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('warehouse_type_id')->unsigned()->nullable();
            $table->bigInteger('article_id')->unsigned()->nullable();
            $table->date('creation_date')->nullable();
            $table->decimal('stock_good', 12, 4)->nullable();
            $table->decimal('stock_damaged', 12, 4)->nullable();
            $table->decimal('found_stock_good', 12, 4)->nullable();
            $table->decimal('found_stock_damaged', 12, 4)->nullable();
            $table->string('observations')->nullable();
            $table->integer('state')->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('warehouse_type_id')->references('id')->on('warehouse_types');
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
        Schema::dropIfExists('inventories');
    }
}
