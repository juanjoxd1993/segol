<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ResetAsistTypesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('asist_types')->delete();
        DB::table('asist_types')->insert([
            ['id' => '1', 'name' => 'Falta', 'short_name' => 'F', 'created_at' => now(), 'created_at_user' => 'Admin'],
            ['id' => '2', 'name' => 'Tardanza', 'short_name' => 'T', 'created_at' => now(), 'created_at_user' => 'Admin'],
            ['id' => '3', 'name' => 'Horas Extra', 'short_name' => 'H', 'created_at' => now(), 'created_at_user' => 'Admin'],
            ['id' => '4', 'name' => 'Horas Extra 35%', 'short_name' => 'H', 'created_at' => now(), 'created_at_user' => 'Admin'],
            ['id' => '6', 'name' => 'Bonificación Nocturna ', 'short_name' => 'B', 'created_at' => now(), 'created_at_user' => 'Admin'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('asist_types')->delete();
    }
}
