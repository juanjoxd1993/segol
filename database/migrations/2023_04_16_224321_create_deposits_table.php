<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('bank_date')->nullable()->default(null);
            $table->unsignedBigInteger('bank_id')->nullable()->default(null);
            $table->unsignedBigInteger('bank_account_id')->nullable()->default(null);
            $table->string('operation_number')->nullable()->default(null);
            $table->decimal('total', 12, 4)->nullable()->default(null);
            $table->decimal('total_pend', 12, 4)->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bank_id')
                    ->references('id')
                    ->on('banks');

            $table->foreign('bank_account_id')
                    ->references('id')
                    ->on('bank_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposits');
    }
}
