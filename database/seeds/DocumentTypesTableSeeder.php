<?php

use Illuminate\Database\Seeder;
use App\DocumentType;

class DocumentTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DocumentType::create([
        	'name'	=> 'RUC',
            'type'  => 6
        ]);

        DocumentType::create([
        	'name'	=> 'DNI',
            'type'  => 1
        ]);

        DocumentType::create([
        	'name'	=> 'OWN'
        ]);
    }
}
