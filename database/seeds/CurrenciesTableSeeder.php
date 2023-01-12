<?php

use Illuminate\Database\Seeder;
use App\Currency;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::create([
        	'name'			=> 'Soles',
        	'short_name'	=> 'PEN',
        	'symbol'		=> 'S/',
        	'reference'		=> '1'
        ]);

        Currency::create([
        	'name'			=> 'Dólares',
        	'short_name'	=> 'USD',
        	'symbol'		=> '$',
        	'reference'		=> '2'
        ]);

        Currency::create([
        	'name'			=> 'Euros',
        	'short_name'	=> 'EUR',
        	'symbol'		=> '€',
        	'reference'		=> '3'
        ]);
    }
}
