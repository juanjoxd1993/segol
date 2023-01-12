<?php

use Illuminate\Database\Seeder;
use App\AddressType;

class AddressTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AddressType::create([
            'name'  => 'Dirección Fiscal'
        ]);

        AddressType::create([
            'name'  => 'Dirección de Despacho'
        ]);

        AddressType::create([
            'name'  => 'Dirección de Cobranza'
        ]);
    }
}
