<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateClientAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_addresses', function(Blueprint $table) {
            $table->dropColumn('user');
            
            $table->bigInteger('item_number')->after('address_type_id')->nullable();
            $table->bigInteger('ubigeo_id')->after('address')->unsigned()->nullable();
            $table->string('created_at_user')->after('updated_at')->nullable();
            $table->string('updated_at_user')->after('created_at_user')->nullable();

            $table->foreign('ubigeo_id')->references('id')->on('ubigeos');
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
