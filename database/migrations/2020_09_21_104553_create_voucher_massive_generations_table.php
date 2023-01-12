<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherMassiveGenerationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_massive_generations', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->bigInteger('company_id')->unsigned()->nullable();
			$table->date('initial_date')->nullable();
			$table->date('final_date')->nullable();
			$table->bigInteger('business_unit_id')->unsigned()->nullable();
			$table->bigInteger('voucher_type_id')->unsigned()->nullable();
			$table->bigInteger('client_id')->unsigned()->nullable();
			$table->decimal('kg', 12, 2)->nullable();
			$table->decimal('price', 12, 4)->nullable();
			$table->date('issue_date')->nullable();
			$table->bigInteger('article_id')->unsigned()->nullable();
            $table->timestamps();
			$table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
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
        Schema::dropIfExists('voucher_massive_generations');
    }
}
