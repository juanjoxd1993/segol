<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('classification_type_id')->unsigned()->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->string('created_at_user')->nullable();
            $table->string('updated_at_user')->nullable();
            $table->softDeletes();

            $table->foreign('classification_type_id')->references('id')->on('classification_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classifications');
    }
}