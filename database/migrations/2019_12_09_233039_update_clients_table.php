<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function(Blueprint $table) {
            $table->dropColumn('user');

            $table->bigInteger('link_client_id')->after('phone_number_2')->unsigned()->nullable();
            $table->string('sales_area')->after('link_client_id')->nullable();
            $table->string('sales_group')->after('sales_area')->nullable();
            $table->string('business_type')->after('seller_id')->nullable();
            $table->string('created_at_user')->after('updated_at')->nullable();
            $table->string('updated_at_user')->after('created_at_user')->nullable();

            $table->foreign('link_client_id')->references('id')->on('clients');
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
