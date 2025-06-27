<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\CierreCaja;
use App\Company;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;


class RegisterOpeningCajaController extends Controller
{

    public function index()

    {

        $date = CarbonImmutable::now()->startOfDay();

        $data = [
            'companies' => Company::whereIn('id',[2])->get(),
            'max_datetime' => $date->startOfDay()->addDays(0)->toAtomString()
        ];

        return view('backend.register_opening_caja', $data);
    }

    public function list()
    {
        $elements = CierreCaja::join('companies', 'cierre_cajas.company_id', '=', 'companies.id')
            ->select(
                'cierre_cajas.id as cierre_id',
                'cierre_cajas.cierre_date as cierre_date',
                DB::raw("CASE cierre_cajas.state WHEN 0 THEN 'Aperturado' WHEN 1 THEN 'Cerrado' END as caja_state"),
                'companies.name as company_name',
                'cierre_cajas.created_at_user as caja_user'
            )
            ->where('cierre_cajas.cierre_date', '>=', Carbon::now()->subDays(7)->toDateString())
            ->orderBy('cierre_cajas.cierre_date', 'asc')
            ->get();

        return $elements;
    }

    public function store()
    {
        request()->validate([
            'sale_date' => ['required'],
            'company_id' => ['required'],
        ], [
            'sale_date.required' => 'Debe seleccionar la fecha de apertura',
            'company_id.required' => 'Debe seleccionar empresa',
        ]);

        $sale_date = CarbonImmutable::createFromDate(request('sale_date'))->startOfDay()->format('Y-m-d');
        $yesterday_sale_date = CarbonImmutable::createFromDate(request('sale_date'))->startOfDay()->subDay()->format('Y-m-d');
        $company_id = request('company_id');

        $element = CierreCaja::where('cierre_date', $yesterday_sale_date)
            ->where('company_id', $company_id)
            ->where('state', 1)
            ->first();

        if ($element) { //esta cerrado

            $obj = CierreCaja::where('cierre_date', $sale_date)
                ->where('company_id', $company_id)
                ->first();

            if ($obj) {

                if($obj->state == 0) { // ya esta aperturado
                    $type = 2;
                    $title = "Error!";
                    $msg = "El dia ya se encuentra aperturado";
                    $url = "";
                } else {

                    $type = 2;
                    $title = 'Ok!';
                    $msg = 'El dia ya se encuentra cerrado';
                    $url = '';
                }

            } else {
                $caja = new CierreCaja();
                $caja->company_id = $company_id;
                $caja->cierre_date = $sale_date;
                $caja->state  = 0; // aperturado
                $caja->created_at_user = Auth::user()->name;
                $caja->save();

                $type = 3;
                $title = 'Ok!';
                $msg = 'Dia aperturado correctamente';
                $url = route('dashboard.opening.caja');
            }
        } else {
            $type = 2;
            $title = "Error!";
            $msg = "Debe cerrar la caja del dia anterior para poder aperturar el dia actual";
            $url = "";
        }

        return response()->json([
            'type'  => $type,
            'title' => $title,
            'msg'   => $msg,
            'url'   => $url
        ], 200);
    }
}
