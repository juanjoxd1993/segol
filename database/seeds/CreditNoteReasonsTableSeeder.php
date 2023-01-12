<?php

use Illuminate\Database\Seeder;
use App\CreditNoteReason;

class CreditNoteReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CreditNoteReason::create([
            'name'  => 'Anulación de la Operación',
            'type'  => '01'
        ]);

        CreditNoteReason::create([
            'name'  => 'Anulación por error en el RUC',
            'type'  => '02'
        ]);

        CreditNoteReason::create([
            'name'  => 'Corrección por error en la descripción',
            'type'  => '03'
        ]);

        CreditNoteReason::create([
            'name'  => 'Descuento global',
            'type'  => '04'
        ]);

        CreditNoteReason::create([
            'name'  => 'Descuento por item',
            'type'  => '05'
        ]);

        CreditNoteReason::create([
            'name'  => 'Devolución total',
            'type'  => '06'
        ]);

        CreditNoteReason::create([
            'name'  => 'Devolución por item',
            'type'  => '07'
        ]);

        CreditNoteReason::create([
            'name'  => 'Bonificación',
            'type'  => '08'
        ]);

        CreditNoteReason::create([
            'name'  => 'Disminución en el valor',
            'type'  => '09'
        ]);
    }
}
