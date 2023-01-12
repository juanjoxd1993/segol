<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InventoryReportExport implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $elements;
    protected $company;
    protected $warehouse_type;
    protected $creation_date;
    protected $state;

    public function __construct($elements, $company, $warehouse_type, $creation_date, $state)
    {
        $this->elements = $elements;
        $this->company = $company;
        $this->warehouse_type = $warehouse_type;
        $this->creation_date = $creation_date;
        $this->state = $state;
    }

    public function view(): View
    {
        $elements = $this->elements;
        $company = $this->company;
        $warehouse_type = $this->warehouse_type;
        $creation_date = $this->creation_date;
        $state = $this->state;

        return view('backend.excel.inventories')->with(compact('elements', 'company', 'warehouse_type', 'creation_date', 'state'));
    }
}
