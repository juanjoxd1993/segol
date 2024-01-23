<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNotaInternaBonifToWarehouseDocumentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('warehouse_document_types')->insert([
            'name' => 'Nota Interna Bonif.',
            'short_name' => 'NIB',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('warehouse_document_types')->where('name', '=', 'Nota Interna Bonif.')->delete();
    }
}
