<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportsGlpGlobalController extends Controller {
    public function index() {
		return view('backend.reports_glp_global');
	}
}