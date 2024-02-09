<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Imports\ShoppingImport;
use Maatwebsite\Excel\Facades\Excel;

class FacturationController extends Controller
{
    public function showImport()
    {
        return view('backend.sales_import');
    }

    public function postImport()
    {
        request()->validate([
            'archivo' => ['required', 'file', 'mimetypes:text/plain,text/csv', 'mimes:txt,csv'],
        ]);

        $file = request()->file('archivo');
        $result = Excel::import(new ShoppingImport, $file);
        
        return response()->json(true, 200);
    }
}
