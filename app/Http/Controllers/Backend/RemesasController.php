<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

use Auth;
use App\Remesa;

use DB;

use stdClass;

class RemesasController extends Controller
{
  public function index() {
    return view('backend.remesas');
  }

	public function validateForm() {
		$messages = [
			'amount.required' => 'Debe ingresar un monto valido.',
			'date.required'   => 'Debe ingresar una fecha valida.',
		];

		$rules = [
			'amount' => 'required',
			'date'   => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

  public function store() {
		$this->validateForm();

		$user_id = Auth::user()->id;
    $amount = request('amount');
    $date = request('date');

    $remesa = new Remesa();
    $remesa->amount = $amount;
    $remesa->date = $date;
    $remesa->user_id = $user_id;
    $remesa->save();

    return response()->json($remesa, 200);
  }
}