<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Company;
use App\VoucherType;
use App\Exports\SalesVolumeReportExport;
use App\Voucher;
use App\VoucherDetail;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class CordiaVolumeReportController extends Controller
{
	public function index() {
		$companies = Company::select('id','name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));

		return view('backend.cordia_volume_report')->with(compact('companies', 'current_date'));
	}

	public function validateForm() {
		$messages = [
			'company_id.required'       => 'Debe seleccionar una Compañía.',
			'since_date.required'       => 'Debe seleccionar una Fecha de Inicio.',
		];

		$rules = [
			'company_id'        => 'required',
			'since_date'		=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$company_id = request('model.company_id');
		$since_date = date('Y-m-d', strtotime(request('model.since_date')));
		$to_date = ( request('model.to_date') ? date('Y-m-d', strtotime(request('model.to_date'))) : date('Y-m-d') );

		$elements = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
			->leftjoin('articles', function($join) {
				$join->on('voucher_details.name', '=', 'articles.name')
					->where('articles.warehouse_type_id', 5);
			})
			->leftjoin('voucher_types', 'vouchers.voucher_type_id', '=', 'voucher_types.id')
			->where('vouchers.company_id', $company_id)
			// ->where('vouchers.ose', 1)
			->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
			->where('vouchers.voucher_type_id', 2)
			->where('vouchers.issue_date', '>=', $since_date)
			->where('vouchers.issue_date', '<=', $to_date)
			->whereNotIn('vouchers.ose', [3])
			->select('voucher_types.type as voucher_type_type', 'voucher_types.name as voucher_type_name', 'serie_number','clients.document_number as document_number','clients.business_name as client_name',
			DB::Raw('MIN(voucher_number) as initial_voucher_number'), DB::Raw('MAX(voucher_number) as final_voucher_number'), 'issue_date', 'articles.code as article_code', 'voucher_details.name as article_name' )
		//	->groupBy('voucher_type_type', 'serie_number', 'issue_date', 'article_code', 'article_name')
			->groupBy('serie_number' ,'issue_date')
			->orderBy('issue_date')
			->orderBy('voucher_type_type')
			->orderBy('serie_number')
			->orderBy('initial_voucher_number')
			->orderBy('final_voucher_number')
		//	->orderBy('article_code')
			->get();

		// Crear datos para la paginación de KT Datatable
        $meta = new \stdClass();
        $meta->page = 1;
        $meta->pages = 1;
        $meta->perpage = -1;
        $meta->total = $elements->count();
        $meta->field = 'voucher_id';
        for ($i = 1; $i <= $elements->count() ; $i++) {
            $meta->rowIds[] = $i;
        }

		return response()->json([
            'meta' => $meta,
            'data' => $elements,
        ]);

	}

	public function export() {
		$company_id = request('model.company_id');
		$since_date = date('Y-m-d', strtotime(request('model.since_date')));
		$to_date = ( request('model.to_date') ? date('Y-m-d', strtotime(request('model.to_date'))) : date('Y-m-d') );

		$elements = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
			->leftjoin('articles', function($join) {
				$join->on('voucher_details.name', '=', 'articles.name')
					->where('articles.warehouse_type_id', 5);
			})
			->leftjoin('voucher_types', 'vouchers.voucher_type_id', '=', 'voucher_types.id')
			->where('vouchers.company_id', $company_id)
			// ->where('vouchers.ose', 1)
			->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
			->where('vouchers.voucher_type_id', 2)
			->where('vouchers.issue_date', '>=', $since_date)
			->where('vouchers.issue_date', '<=', $to_date)
			->whereNotIn('vouchers.ose', [3])
			->select('voucher_types.type as voucher_type_type', 'clients.business_name as client_name', 
			'clients.document_number as document_number','clients.id as client_id',
			'voucher_types.name as voucher_type_name', 
			'serie_number', DB::Raw('MIN(voucher_number) as initial_voucher_number'),
			 DB::Raw('MAX(voucher_number) as final_voucher_number'), 'issue_date', 'articles.code as article_code', 'voucher_details.name as article_name', 
			 DB::Raw('SUM(voucher_details.quantity) as sum_quantity'))
		//	->groupBy('voucher_type_type', 'serie_number', 'issue_date', 'article_code', 'article_name')
			->groupBy('serie_number' ,'issue_date')
			->orderBy('issue_date')
			->orderBy('voucher_type_type')
			->orderBy('serie_number')
			->orderBy('initial_voucher_number')
			->orderBy('final_voucher_number')
			->orderBy('article_code')
			->get();



		
			$elements->map(function($item) {

				$sum_taxed=Voucher::leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.issue_date', '=', $item->issue_date )
				->where('vouchers.serie_number', '=', $item->serie_number)
				->whereNotIn('vouchers.client_id', [1031, 427, 13326, 13775, 14072])
				->select ('taxed_operation')
				->sum('taxed_operation');
	
				
				$sum_total=Voucher::leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.issue_date', '=', $item->issue_date )
				->where('vouchers.serie_number', '=', $item->serie_number)
				->whereNotIn('vouchers.client_id', [1031, 427, 13326, 13775, 14072])
				->select ('total')
				->sum('total');
	
				$sum_igv=Voucher::leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.issue_date', '=', $item->issue_date )
				->where('vouchers.serie_number', '=', $item->serie_number)
				->whereNotIn('vouchers.client_id', [1031, 427, 13326, 13775, 14072])
				->select ('igv')
				->sum('igv');
	
	
				$sum_24k = VoucherDetail::leftjoin('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.serie_number', '=', $item->serie_number)
				->where('vouchers.issue_date', '=', $item->issue_date)
				->where('voucher_details.article_id',24)
				->whereNotIn('vouchers.client_id', [1031, 427, 13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');
	
				$sum_1k = VoucherDetail::leftjoin('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.serie_number', '=', $item->serie_number)
				->where('vouchers.issue_date', '=', $item->issue_date)
				->where('voucher_details.article_id',23)
				->whereNotIn('vouchers.client_id', [1031, 427, 13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');
	
				$sum_5k = VoucherDetail::leftjoin('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.serie_number', '=', $item->serie_number)
				->where('vouchers.issue_date', '=', $item->issue_date)
				->where('articles.subgroup_id', 55)
				->select('quantity')
				->sum('quantity');
	
				$sum_10k = VoucherDetail::leftjoin('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.serie_number', '=', $item->serie_number)
				->where('vouchers.issue_date', '=', $item->issue_date)
				->where('articles.subgroup_id', 56)
				->select('quantity')
				->sum('quantity');
	
				$sum_15k = VoucherDetail::leftjoin('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.serie_number', '=', $item->serie_number)
				->where('vouchers.issue_date', '=', $item->issue_date)
				->where('articles.subgroup_id', 57)
				->select('quantity')
				->sum('quantity');
	
				$sum_45k = VoucherDetail::leftjoin('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->where('vouchers.serie_number', '=', $item->serie_number)
				->where('vouchers.issue_date', '=', $item->issue_date)
				->where('articles.subgroup_id', 58)
				->select('quantity')
				->sum('quantity');
	
				$item->fchemi = date('d-m-Y', strtotime($item->issue_date));
				$item->sum_quantity = number_format($item->sum_quantity, 4, '.', '');
				$item->sum_sale_value = number_format($sum_taxed, 4, '.', '');
				$item->sum_igv = number_format($sum_igv, 4, '.', '');
				$item->sum_total = number_format($sum_total, 2, '.', '');
				$item->sum_1k = number_format($sum_1k, 2, '.', '');
				$item->sum_24k = number_format($sum_24k, 2, '.', '');
				$item->sum_5k = number_format($sum_5k, 2, '.', '');
				$item->sum_10k = number_format($sum_10k, 2, '.', '');
				$item->sum_15k = number_format($sum_15k, 2, '.', '');
				$item->sum_45k = number_format($sum_45k, 2, '.', '');
			});


	

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->mergeCells('A1:S1');
		$sheet->setCellValue('A1', 'REPORTE DE VOLUMEN DE BOLETAS');
		$sheet->getStyle('A1')->applyFromArray([
			'font' => [
				'bold' => true,
				'size' => 16,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			]
		]);

		
		$sheet->setCellValue('A3', '#');
		$sheet->setCellValue('B3', 'ID Doc.');
		$sheet->setCellValue('C3', 'Tipo de Doc.');
		$sheet->setCellValue('F3', 'Serie');
		$sheet->setCellValue('G3', 'Nº Inicial');
		$sheet->setCellValue('H3', 'Nº Final');
		$sheet->setCellValue('I3', 'Fec. Emisión');
		$sheet->setCellValue('K3', 'Valor Venta');
		$sheet->setCellValue('L3', 'IGV');
		$sheet->setCellValue('M3', 'Total');
		$sheet->setCellValue('N3', '1K');
		$sheet->setCellValue('O3', '5K');
		$sheet->setCellValue('P3', '10K');
		$sheet->setCellValue('Q3', '15K');
		$sheet->setCellValue('R3', '45K');
		$sheet->setCellValue('S3', 'Total Kg.');
		$sheet->getStyle('A3:S3')->applyFromArray([
			'font' => [
				'bold' => true,
			],
		]);

		$row_number = 4;
		foreach ($elements as $index => $element) {
			$index++;
			$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
			$sheet->setCellValue('B'.$row_number, $element->voucher_type_type);
			$sheet->setCellValue('C'.$row_number, $element->voucher_type_name);
         //   $sheet->setCellValue('D'.$row_number, $element->document_number);
		//	$sheet->setCellValue('E'.$row_number, $element->client_name);
			$sheet->setCellValue('F'.$row_number, $element->serie_number);
			$sheet->setCellValue('G'.$row_number, $element->initial_voucher_number);
			$sheet->setCellValue('H'.$row_number, $element->final_voucher_number);
			$sheet->setCellValue('I'.$row_number, $element->issue_date);
		//	$sheet->setCellValue('H'.$row_number, $element->article_code);
		//	$sheet->setCellValue('J'.$row_number, $element->article_name);
		//	$sheet->setCellValue('I'.$row_number, $element->sum_quantity);
			$sheet->setCellValue('K'.$row_number, $element->sum_sale_value);
			$sheet->setCellValue('L'.$row_number, $element->sum_igv);
			$sheet->setCellValue('M'.$row_number, $element->sum_total);
		//	$sheet->setCellValue('N'.$row_number, $element->gallons);
			$sheet->setCellValue('N'.$row_number, $element->sum_1k);
			$sheet->setCellValue('O'.$row_number, $element->sum_5k);
			$sheet->setCellValue('P'.$row_number, $element->sum_10k);
			$sheet->setCellValue('Q'.$row_number, $element->sum_15k);
			$sheet->setCellValue('R'.$row_number, $element->sum_45k);
			$sheet->setCellValue('S'.$row_number, $element->sum_tm_total);

			$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
			$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
			$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
	//		$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.0000');

			$row_number++;
		}

		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getColumnDimension('I')->setAutoSize(true);
		$sheet->getColumnDimension('J')->setAutoSize(true);
		$sheet->getColumnDimension('K')->setAutoSize(true);
		$sheet->getColumnDimension('L')->setAutoSize(true);
		$sheet->getColumnDimension('M')->setAutoSize(true);
		$sheet->getColumnDimension('N')->setAutoSize(true);
		$sheet->getColumnDimension('O')->setAutoSize(true);
		$sheet->getColumnDimension('P')->setAutoSize(true);
		$sheet->getColumnDimension('Q')->setAutoSize(true);
		$sheet->getColumnDimension('R')->setAutoSize(true);
		$sheet->getColumnDimension('S')->setAutoSize(true);
		$sheet->getColumnDimension('T')->setAutoSize(true);

		$writer = new Xls($spreadsheet);
		return $writer->save('php://output');
	}
}
