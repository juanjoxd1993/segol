<?php

use Illuminate\Database\Seeder;
use App\Company;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Company::create([
            'user_id' => '1',
            'document_type_id' => '1',
            'document_number'   => '20293658146',
            'name' => 'PUNTO DE DISTRIBUCION S.A.C.',
            'short_name' => 'PuntoD',
            'activity_date' => '1995-09-28',
            'perception_agent' => '2',
            'retention_agent' => '0',
            'bizlinks_user' => 'PUNTO_DE_DISTRIBUCION_20293658146',
            'bizlinks_password' => 'icoQMSG7nbjoUaaK',
            'bizlinks_user_test' => '20293658146BIZLINKS',
            'bizlinks_password_test' => 'TESTBIZLINKS',
            'certificate_pem' => 'PD_2019_cert.pem',
            'database_name' => 'db_pd',
            'state' => '1',
        ]);

        Company::create([
            'user_id' => '1',
            'document_type_id' => '1',
            'document_number'   => '20515763903',
            'name' => 'CORPORACION DISTRIBUIDORA ATLANTICA S.A.C.',
            'short_name' => 'Cordia',
            'activity_date' => '2007-04-18',
            'perception_agent' => '2',
            'retention_agent' => '0',
            'bizlinks_user' => 'CORDIA_20515763903',
            'bizlinks_password' => '3XMhybCC2IeOo4K5',
            'bizlinks_user_test' => '20515763903BIZLINKS',
            'bizlinks_password_test' => 'vuNfM8jj',
            'certificate_pem' => 'Cordia_2019_cert.pem',
            'database_name' => 'db_cordia',
            'state' => '1',
        ]);

        Company::create([
            'user_id' => '1',
            'document_type_id' => '1',
            'document_number'   => '20536075195',
            'name' => 'GLOBAL START UP S.A.C.',
            'short_name' => 'GloStar',
            'activity_date' => '2010-05-11',
            'perception_agent' => '2',
            'retention_agent' => '0',
            'bizlinks_user' => 'GLOSTAR_20536075195',
            'bizlinks_password' => 'RvHjmsJ1z7oaBSAO',
            'bizlinks_user_test' => '20536075195BIZLINKS',
            'bizlinks_password_test' => '8eGGEI3A',
            'certificate_pem' => 'Global_2019_cert.pem',
            'database_name' => 'db_rinde',
            'state' => '1',
        ]);
    }
}
