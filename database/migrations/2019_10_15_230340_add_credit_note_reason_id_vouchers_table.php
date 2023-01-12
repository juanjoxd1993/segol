<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreditNoteReasonIdVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->bigInteger('credit_note_reason_id')->after('voucher_number')->unsigned()->nullable();
            $table->foreign('credit_note_reason_id')->references('id')->on('credit_note_reasons');
            $table->string('credit_note_reference_serie')->after('credit_note_reason_id')->nullable();
            $table->string('credit_note_reference_number')->after('credit_note_reference_serie')->nullable();
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
