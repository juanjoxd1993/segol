<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('document_type_id')->unsigned();
            $table->string('document_number')->nullable();
            $table->string('business_name')->nullable();
            $table->string('address')->nullable();
            $table->bigInteger('ubigeo_id')->unsigned()->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number_1')->nullable();
            $table->string('phone_number_2')->nullable();
            $table->string('retention_agent')->nullable();
            $table->bigInteger('perception_agent_id')->unsigned()->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();
            
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->foreign('ubigeo_id')->references('id')->on('ubigeos');
            $table->foreign('perception_agent_id')->references('id')->on('rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers');
    }
}
