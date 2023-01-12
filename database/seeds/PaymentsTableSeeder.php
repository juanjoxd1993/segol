<?php

use Illuminate\Database\Seeder;
use App\Payment;

class PaymentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Payment::create([
        	'name'		=> 'Efectivo al contado',
        	'reference'	=> '1'
        ]);

        Payment::create([
        	'name'		=> 'Crédito',
        	'reference'	=> '2'
        ]);

        Payment::create([
        	'name'		=> 'Depósito/Cheque',
        	'reference'	=> '3'
        ]);

        Payment::create([
        	'name'		=> 'Transferencia',
        	'reference'	=> '4'
        ]);
    }
}
