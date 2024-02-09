<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsTablaAddIntNameBolNameBolNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function(Blueprint $table) {
            $table->string('int_name')->default(null);
            $table->string('bol_name')->default(null);
            $table->string('bol_number')->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function(Blueprint $table) {
            $table->dropColumn('int_name');
            $table->dropColumn('bol_name');
            $table->dropColumn('bol_number');
        });
    }
}
