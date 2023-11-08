<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use PDF;

class CertificadoReportController extends Controller
{
    public function index() {
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.certificado_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha inicial.',
			'client_id.required' => 'Debe seleccionar un empleado.',
		];

		$rules = [
			'initial_date'	=> 'required',
			'client_id' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getClients() {
		$q = request('q');

		$clients = Employee::select('id', 'first_name as text')
			->where('first_name', 'like', '%'.$q.'%')
			->withTrashed()
			->get();

		return $clients;
	}

	public function list() {
		$export = request('export');
	    $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay();
		$client_id = request('model.client_id');
		$employ = Employee::find($client_id);

		if ($export) {
			$pdf = PDF::loadView('backend.pdf.employ_certificate', [
				'date' => $initial_date,
				'employ' => $employ,
				'base64_logo' => base64_encode(file_get_contents(public_path('backend/img/logo-pdf-puntod.png'))),
			]);


			$pdf->setPaper('A4', 'portrait');

			return $pdf->stream();
		} else {
			return response()->json(null, 200);
		}
	}
}
	








