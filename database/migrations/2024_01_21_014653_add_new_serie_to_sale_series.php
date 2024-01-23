<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewSerieToSaleSeries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Obtiene el ID del nuevo tipo de documento
        $documentTypeId = DB::table('warehouse_document_types')
                            ->where('name', 'Nota Interna Bonif.')
                            ->value('id');

        DB::table('sale_series')->insert([
            'num_serie' => 'I001', 
            'correlative' => 1, 
            'created_at' => now(),
            'updated_at' => now(),
            'warehouse_document_type_id' => $documentTypeId
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Obtiene el ID del nuevo tipo de documento
        $documentTypeId = DB::table('warehouse_document_types')
                            ->where('name', 'Nota Interna Bonif.')
                            ->value('id');

        DB::table('sale_series')->where('warehouse_document_type_id', '=', $documentTypeId)->delete();
    }
}
