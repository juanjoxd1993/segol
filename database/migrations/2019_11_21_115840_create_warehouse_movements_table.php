<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('warehouse_type_id')->unsigned()->nullable();
            $table->bigInteger('movement_class_id')->unsigned()->nullable();
            $table->bigInteger('movement_type_id')->unsigned()->nullable();
            $table->bigInteger('movement_number')->nullable();
            $table->bigInteger('warehouse_account_type_id')->unsigned()->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->string('account_document_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('referral_guide_series')->nullable();
            $table->string('referral_guide_number')->nullable();
            $table->bigInteger('referral_warehouse_document_type_id')->unsigned()->nullable();
            $table->string('referral_serie_number')->nullable();
            $table->string('referral_voucher_number')->nullable();
            $table->string('scop_number')->nullable();
            $table->string('license_plate')->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->decimal('taxed_operation', 12, 2)->nullable();
            $table->decimal('unaffected_operation', 12, 2)->nullable();
            $table->decimal('exonerated_operation', 12, 2)->nullable();
            $table->decimal('igv', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->string('voucher_number')->nullable();
            $table->integer('situation')->nullable();
            $table->integer('origin')->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('warehouse_type_id')->references('id')->on('warehouse_types');
            $table->foreign('movement_class_id')->references('id')->on('movent_classes');
            $table->foreign('movement_type_id')->references('id')->on('movent_types');
            $table->foreign('warehouse_account_type_id')->references('id')->on('warehouse_account_types');
            $table->foreign('referral_warehouse_document_type_id')->references('id')->on('warehouse_document_types');
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_movements');
    }
}
