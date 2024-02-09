<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\VoucherType;

class BillingController extends Controller
{
    public function billing_ose() {
    	$companies = Company::select('id','name')->get();
    	$voucher_types = VoucherType::select('id','name')->get();
    	return view('backend.billing_ose')->with(compact('companies','voucher_types'));
    }

    public function clients() {
    	return view('backend.clients');
    }

    public function test() {
    	return 'hola';
    }
}
