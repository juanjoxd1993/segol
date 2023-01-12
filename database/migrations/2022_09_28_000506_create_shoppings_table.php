<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShoppingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoppings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->date('expiry_date')->nullable();
            $table->string('cost_code')->nullable();
            $table->string('voucher');
            $table->string('provider_ruc');
            $table->string('provider_name');
            $table->double('base_with_fiscal_credit_right', 10, 2);
            $table->double('base_without_fiscal_credit_right', 10, 2);
            $table->double('base_operation_mixed', 10, 2);
            $table->double('non_task_adquisitions', 10, 2);
            $table->double('igv_with_fiscal_credit_right', 10, 2);
            $table->double('igv_without_fiscal_credit_right', 10, 2);
            $table->double('igv_task_mixed', 10, 2);
            $table->double('other_charges', 10, 2);
            $table->double('total', 10, 2);
            $table->string('glosa');
            $table->string('doc_ref')->nullable();
            $table->date('doc_ref_date')->nullable();
            $table->double('exchange_rate', 10, 2);
            $table->string('comp_con');
            $table->double('base_ref', 10, 2);
            $table->string('number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shoppings');
    }
}
