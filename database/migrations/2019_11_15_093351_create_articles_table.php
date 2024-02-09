<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('warehouse_type_id')->unsigned()->nullable();
            $table->bigInteger('code')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('package_sale')->nullable();
            $table->bigInteger('sale_unit_id')->unsigned()->nullable();
            $table->bigInteger('package_warehouse')->nullable();
            $table->bigInteger('warehouse_unit_id')->unsigned()->nullable();
            $table->bigInteger('operation_type_id')->unsigned()->nullable();
            $table->decimal('factor', 8, 4)->nullable();
            $table->bigInteger('family_id')->unsigned()->nullable();
            $table->bigInteger('group_id')->unsigned()->nullable();
            $table->bigInteger('subgroup_id')->unsigned()->nullable();
            $table->integer('igv')->nullable();
            $table->integer('perception')->nullable();
            $table->decimal('stock_good', 12, 4)->nullable();
            $table->decimal('stock_repair', 12, 4)->nullable();
            $table->decimal('stock_return', 12, 4)->nullable();
            $table->decimal('stock_damaged', 12, 4)->nullable();
            $table->decimal('stock_minimum', 12, 4)->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('warehouse_type_id')->references('id')->on('warehouse_types');
            $table->foreign('sale_unit_id')->references('id')->on('units');
            $table->foreign('warehouse_unit_id')->references('id')->on('units');
            $table->foreign('operation_type_id')->references('id')->on('operation_types');
            $table->foreign('family_id')->references('id')->on('classifications');
            $table->foreign('group_id')->references('id')->on('classifications');
            $table->foreign('subgroup_id')->references('id')->on('classifications');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
