<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->bigInteger('document_type_id')->unsigned();
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->string('document_number')->nullable();
            $table->string('name')->nullable();
            $table->string('short_name')->nullable();
            $table->date('activity_date')->nullable();
            $table->string('perception_agent')->nullable();
            $table->string('retention_agent')->nullable();
            $table->string('bizlinks_user')->nullable();
            $table->string('bizlinks_password')->nullable();
            $table->string('bizlinks_user_test')->nullable();
            $table->string('bizlinks_password_test')->nullable();
            $table->string('certificate_pem')->nullable();
            $table->string('database_name')->nullable();
            $table->integer('state')->nullable();
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
        Schema::dropIfExists('companies');
    }
}
