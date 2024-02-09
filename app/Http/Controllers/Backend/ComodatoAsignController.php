<?php

namespace App\Http\Controllers\Backend;

use App\BusinessUnit;
use App\Client;
use App\ClientAddress;
use App\Company;
use App\CreditNoteReason;
use App\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Rate;
use App\Sale;
use App\SaleDetail;
use App\Unit;
use App\ValueType;
use App\Voucher;
use App\VoucherDetail;
use App\VoucherType;
use App\WarehouseDocumentType;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use stdClass;

class ComodatoAsignController extends Controller
{
  public function index()
  {
		$companies = Company::select('id', 'name')->get();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->where('type_id', 4)->get();

		return view('backend.comodato_asign')->with(compact('companies', 'warehouse_document_types'));
  }
}