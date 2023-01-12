<?php

use Illuminate\Database\Seeder;
use App\VoucherType;

class VoucherTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        VoucherType::create([
        	'name'	=> 'Factura Electrónica',
            'type'  => '01',
            'serie_type'  => 'F',
            'reference' => '3',
        ]);

        VoucherType::create([
        	'name'	=> 'Boleta de Venta Electrónica',
            'type'  => '03',
            'serie_type'  => 'B',
            'reference' => '2',
        ]);

        VoucherType::create([
            'name'  => 'Nota de Crédito',
            'type'  => '07',
            'serie_type'  => 'FC',
            'reference' => '5'
        ]);

        VoucherType::create([
            'name'  => 'Nota de Débito',
            'type'  => '08',
            'serie_type'  => 'FD',
            'reference' => '6'
        ]);
    }
}
