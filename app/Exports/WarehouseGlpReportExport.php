<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class WarehouseGlpReportExport implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

	protected $warehouse_type_name;
	protected $datetime;
	protected $elements;

	public function __construct($warehouse_type_name, $datetime, $elements)
	{
		$this->warehouse_type_name = $warehouse_type_name;
		$this->datetime = $datetime;
		$this->elements = $elements;
	}

    public function view(): View
    {
        $warehouse_type_name = $this->warehouse_type_name;
        $datetime = $this->datetime;
        $elements = $this->elements;

		return view('backend.excel.articles')->with(compact('warehouse_type_name', 'datetime', 'elements'));
    }
}
