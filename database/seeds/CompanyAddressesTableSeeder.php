<?php

use Illuminate\Database\Seeder;
use App\CompanyAddress;

class CompanyAddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CompanyAddress::create([
        	'user_id' => 1,
            'company_id' => 1,
            'type'  => 1,
            'address' => 'AV. FELIPE PARDO Y ALIAGA NRO. 640 INT. 1401 URB. SANTA CRUZ',
            'district' => 'San Isidro',
            'province' => 'Lima',
            'department' => 'Lima',
            'ubigeo'    => '150131',
        ]);

        CompanyAddress::create([
        	'user_id' => 1,
            'company_id' => 2,
            'type'  => 1,
            'address' => 'AV. FELIPE PARDO Y ALIAGA NRO. 640 INT. 1401 URB. SANTA CRUZ',
            'district' => 'San Isidro',
            'province' => 'Lima',
            'department' => 'Lima',
            'ubigeo'    => '150131',
        ]);

        CompanyAddress::create([
        	'user_id' => 1,
            'company_id' => 3,
            'type'  => 1,
            'address' => 'AV. FELIPE PARDO Y ALIAGA NRO. 640 INT. 1401 URB. SANTA CRUZ',
            'district' => 'San Isidro',
            'province' => 'Lima',
            'department' => 'Lima',
            'ubigeo'    => '150131',
        ]);
    }
}
