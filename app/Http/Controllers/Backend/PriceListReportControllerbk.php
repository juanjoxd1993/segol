<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\BusinessUnit;
use App\Client;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PriceList;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PDF;

class PriceListReportController extends Controller
{
    public function index() {
		$business_units = BusinessUnit::select('id', 'name')->get();
		$companies = Company::select('id', 'name')->get();

		return view('backend.price_list_report')->with(compact('business_units', 'companies'));
	}

	public function getClients() {
		$business_unit_id = request('business_unit_id');
		$company_id = request('company_id');
		$q = request('q');

		$clients = Client::select('id', 'business_name as text')
			->where('business_unit_id', $business_unit_id)
			->when($company_id, function ($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('business_name', 'like', '%'.$q.'%')
			->get();

		return $clients;
	}

	public function getArticles() {
		$q = request('q');

		$articles = Article::select('id', DB::Raw('CONCAT(code, " - ", name) as text'))
			->where('warehouse_type_id', 5)
			->where('code', 'like', '%'.$q.'%')
			->orWhere('name', 'like', '%'.$q.'%')
			->get();

		return $articles;
	}

	public function validateForm() {
		$messages = [
			'business_unit_id.required'	=> 'Debe seleccionar una Fecha inicial.',
		];

		$rules = [
			'business_unit_id'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$export_excel = request('export_excel');

		$business_unit_id = request('model.business_unit_id');
		$company_id = request('model.company_id');
		$client_id = request('model.client_id');
		$article_id = request('model.article_id');
		$current_date = CarbonImmutable::now()->format('Y-m-d');

		$elements = PriceList::leftjoin('clients', 'price_lists.client_id', '=', 'clients.id')
			->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
			->leftjoin('companies', 'clients.company_id', '=', 'companies.id')
			->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
			->leftjoin('articles', 'price_lists.article_id', '=', 'articles.id')
			->leftjoin('classifications as family', 'articles.family_id', '=', 'family.id')
			->leftjoin('classifications as subgroup', 'articles.subgroup_id', '=', 'subgroup.id')
			->leftjoin('client_channels', 'clients.channel_id', '=', 'client_channels.id')
			->leftjoin('client_zones', 'clients.zone_id', '=', 'client_zones.id')
			->leftjoin('client_sectors', 'clients.sector_id', '=', 'client_sectors.id')
			->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
			->where('clients.business_unit_id', $business_unit_id)
			->where('price_lists.state', 1)
			->where('price_lists.final_effective_date', '>=', $current_date)
			->when($company_id, function ($query, $company_id) {
				return $query->where('clients.company_id', $company_id);
			})
			->when($client_id, function ($query, $client_id) {
				return $query->where('price_lists.client_id', $client_id);
			})
			->when($article_id, function ($query, $article_id) {
				return $query->where('price_lists.article_id', $article_id);
			})
			->select('price_lists.id', 'business_units.name as business_unit_name', 'companies.short_name as company_short_name', 'clients.id as client_id', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name', 'articles.code as article_code', 'articles.name as article_name', 'price_lists.price_igv', 'price_lists.initial_effective_date', 'family.name as family_name', 'subgroup.name as subgroup_name', 'client_channels.name as client_channel_name', 'client_zones.name as client_zone_name', 'client_sectors.name as client_sector_name', DB::Raw('CONCAT("R-", client_routes.id) as client_route_id'), 'client_routes.name as client_route_name')
			->orderBy('business_unit_name')
			->orderBy('company_short_name')
			->orderBy('business_name')
			->orderBy('article_code')
			->orderBy('family_name')
			->orderBy('subgroup_name')
			->get();

		if ( $export_excel ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:S1');
			$sheet->setCellValue('A1', 'REPORTE LISTA DE PRECIOS VIGENTE AL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('B3', 'Undidad Negocio');
			$sheet->setCellValue('C3', 'Compañía');
			$sheet->setCellValue('D3', 'ID Cliente');
			$sheet->setCellValue('E3', 'Cód. Cliente');
			$sheet->setCellValue('F3', 'Doc. Cliente');
			$sheet->setCellValue('G3', 'Nº Doc.');
			$sheet->setCellValue('H3', 'Razón Social');
			$sheet->setCellValue('I3', 'Cód. Artículo');
			$sheet->setCellValue('J3', 'Descripción');
			$sheet->setCellValue('K3', 'Precio venta');
			$sheet->setCellValue('L3', 'Vigencia desde');
			$sheet->setCellValue('M3', 'Marca');
			$sheet->setCellValue('N3', 'Presentación');
			$sheet->setCellValue('O3', 'Canal venta');
			$sheet->setCellValue('P3', 'Zona venta');
			$sheet->setCellValue('Q3', 'Sector económico');
			$sheet->setCellValue('R3', 'ID Ruta');
			$sheet->setCellValue('S3', 'Ruta');
			
			$sheet->getStyle('A3:S3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($elements as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->business_unit_name);
				$sheet->setCellValue('C'.$row_number, $element->company_short_name);
				$sheet->setCellValue('D'.$row_number, $element->client_id);
				$sheet->setCellValue('E'.$row_number, $element->client_code);
				$sheet->setCellValue('F'.$row_number, $element->document_type_name);
				$sheet->setCellValue('G'.$row_number, $element->document_number);
				$sheet->setCellValue('H'.$row_number, $element->business_name);
				$sheet->setCellValue('I'.$row_number, $element->article_code);
				$sheet->setCellValue('J'.$row_number, $element->article_name);
				$sheet->setCellValue('K'.$row_number, $element->price_igv);
				$sheet->setCellValue('L'.$row_number, $element->initial_effective_date);
				$sheet->setCellValue('M'.$row_number, $element->family_name);
				$sheet->setCellValue('N'.$row_number, $element->subgroup_name);
				$sheet->setCellValue('O'.$row_number, $element->client_channel_name);
				$sheet->setCellValue('P'.$row_number, $element->client_zone_name);
				$sheet->setCellValue('Q'.$row_number, $element->client_sector_name);
				$sheet->setCellValue('R'.$row_number, $element->client_route_id);
				$sheet->setCellValue('S'.$row_number, $element->client_route_name);

				$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.0000');

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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		}
		
		return response()->json($elements);
	}

	public function exportPdf() {
		$business_unit_id = request('model.business_unit_id');
		$company_id = request('model.company_id');
		$client_id = request('model.client_id');
		$article_id = request('model.article_id');
		$current_date = CarbonImmutable::now();
		$current_datetime = $current_date->format('d/m/Y H:m:s');

		$elements = PriceList::leftjoin('clients', 'price_lists.client_id', '=', 'clients.id')
			->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
			->leftjoin('companies', 'clients.company_id', '=', 'companies.id')
			->leftjoin('articles', 'price_lists.article_id', '=', 'articles.id')
			->leftjoin('client_zones', 'clients.zone_id', '=', 'client_zones.id')
			->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
			->where('clients.business_unit_id', $business_unit_id)
			->where('price_lists.state', 1)
			->where('price_lists.final_effective_date', '>=', $current_date->format('Y-m-d'))
			->when($company_id, function ($query, $company_id) {
				return $query->where('clients.company_id', $company_id);
			})
			->when($client_id, function ($query, $client_id) {
				return $query->where('price_lists.client_id', $client_id);
			})
			->when($article_id, function ($query, $article_id) {
				return $query->where('price_lists.article_id', $article_id);
			})
			->select('companies.short_name as company_short_name', 'clients.id as client_id', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name', 'articles.code as article_code', 'articles.name as article_name', 'price_lists.price_igv', 'price_lists.initial_effective_date', DB::Raw('CONCAT("R-", client_routes.id) as client_route_id'))
			->groupBy('clients.id')
			->orderBy('company_short_name')
			->orderBy('business_name')
			->orderBy('article_code')
			->get();

		$pdf = PDF::loadView('backend.pdf.price_list_report', compact('current_datetime', 'elements'));
		return $pdf->stream();
	}
}
