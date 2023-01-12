<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('code')->nullable();
            $table->string('business_name')->nullable();
            $table->bigInteger('document_type_id')->unsigned();
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->string('document_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number_1')->nullable();
            $table->string('phone_number_2')->nullable();
            // $table->bigInteger('client_group_id')->unsigned();
            // $table->foreign('client_group_id')->references('id')->on('client_groups');
            $table->string('client_group_id')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('seller_id')->nullable();
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
