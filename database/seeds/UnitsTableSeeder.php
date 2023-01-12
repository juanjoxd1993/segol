<?php

use Illuminate\Database\Seeder;
use App\Unit;

class UnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Unit::create([
        	'name'			=> 'Unidad',
        	'short_name'	=> 'NIU',
        	'reference'		=> '1'
        ]);

        Unit::create([
        	'name'			=> 'Galón',
        	'short_name'	=> 'GLI',
        	'reference'		=> '2'
        ]);

        Unit::create([
        	'name'			=> 'Metros',
        	'short_name'	=> 'MTK',
        	'reference'		=> '3'
        ]);

        Unit::create([
        	'name'			=> 'Kilogramos',
        	'short_name'	=> 'KGM',
        	'reference'		=> '4'
        ]);

        Unit::create([
        	'name'			=> 'Toneladas',
        	'short_name'	=> 'TNE',
        	'reference'		=> '5'
        ]);

        Unit::create([
        	'name'			=> 'Millar',
        	'short_name'	=> 'MLL',
        	'reference'		=> '6'
        ]);

        Unit::create([
        	'name'			=> 'Cilindro',
        	'short_name'	=> 'CY',
        	'reference'		=> '7'
        ]);

        Unit::create([
        	'name'			=> 'Lata',
        	'short_name'	=> 'CA',
        	'reference'		=> '8'
        ]);

        Unit::create([
        	'name'			=> 'Par',
        	'short_name'	=> 'PR',
        	'reference'		=> '9'
        ]);

        Unit::create([
        	'name'			=> 'Docena',
        	'short_name'	=> 'DZN',
        	'reference'		=> '10'
        ]);
    }
}
