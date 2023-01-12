<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LiquidationSalesReportExport implements FromView, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    /**
    * @return \Illuminate\Support\Collection
    */

	protected $initial_date;
	protected $final_date;
	protected $elements;

	public function __construct($initial_date, $final_date, $elements)
	{
		$this->initial_date = $initial_date;
		$this->final_date = $final_date;
		$this->elements = $elements;
	}

	public function title(): string
    {
        return 'Reporte de Liquidaciones';
    }

    public function view(): View
    {
        $initial_date = $this->initial_date;
        $final_date = $this->final_date;
        $elements = $this->elements;

		return view('backend.excel.liquidations_salesr')->with(compact('initial_date', 'final_date', 'elements'));
    }

	public function columnFormats(): array
    {
		return [
			'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'M' => NumberFormat::FORMAT_NUMBER_0,
			'N' => NumberFormat::FORMAT_NUMBER_00,
			'O' => NumberFormat::FORMAT_NUMBER_00,
	
		];
    }
}
