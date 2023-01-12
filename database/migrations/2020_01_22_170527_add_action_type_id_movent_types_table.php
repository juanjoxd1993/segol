<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActionTypeIdMoventTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movent_types', function(Blueprint $table) {
            $table->bigInteger('action_type_id')->after('short_name')->unsigned()->nullable();
            $table->foreign('action_type_id')->references('id')->on('action_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
