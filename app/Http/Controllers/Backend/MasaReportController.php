<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use App\Container;
use App\ContainerDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\VoucherDetail;
use App\Sale;
use App\Facturation;
use App\SaleDetail;
use App\Voucher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class MasaReportController extends Controller
{
    public function index() {
	//	$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.masa_report')->with(compact('companies', 'current_date'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha inicial.',
			'final_date.required'	=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			'initial_date'	=> 'required',
			'final_date'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getClients() {
		$company_id = request('company_id');
		$q = request('q');

		$clients = Client::select('id', 'business_name as text')
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('business_name', 'like', '%'.$q.'%')
			->withTrashed()
			->get();

		return $clients;
	}

	public function list() {

		$export = request('export');

	    $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
	
		
						$elements = Container::leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
					    ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                        ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                        ->leftjoin('container_details', 'container.id', '=', 'container_details.container_id')
						->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')	            
			            ->select('clients.id as client_id','clients.business_name as business_name','articles.convertion as convertion','warehouse_movement.if_comodato as comodato')
			        
				
			
			->groupBy('business_name')
            ->get();

			$response=[];

            
            $totals_sum_5kt = 0;
			$totals_sum_10kt = 0;
		    $totals_sum_15kt = 0;
            $totals_sum_45kt = 0;
		    $totals_sum_m15t = 0;
		    $totals_sum_5kc = 0;
		    $totals_sum_10kc = 0;
		    $totals_sum_15kc = 0;
			$totals_sum_45kc = 0; 
		    $totals_sum_m15c = 0;
			$totals_sum_5kd = 0;
			$totals_sum_10kd = 0;
		    $totals_sum_15kd = 0;
		    $totals_sum_45kd = 0;
		    $totals_sum_m15kd = 0;
			$totals_sum_1kr = 0;
		    $totals_sum_5kr = 0;
		    $totals_sum_10kr = 0;
		    $totals_sum_15kr = 0;
			$totals_sum_45kr = 0;
            $totals_sum_m15r = 0;



			foreach ($elements as $facturation) {

				$facturation->business_name = $facturation['business_name'];
                $facturation->client_id = $facturation['client_id'];
                $facturation->convertion = $facturation['convertion'];
                $facturation->comodato = $facturation['comodato'];

		       
				$sum_5kt = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',5)
				->where('warehouse_movements.if_comodato', '=',0)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_10kt = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',10)
				->where('warehouse_movements.if_comodato', '=',0)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_15kt = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.presentacion','=',15)
				->where('warehouse_movements.if_comodato', '=',0)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_m15kt = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.presentacion','=',16)
				->where('warehouse_movements.if_comodato', '=',0)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_45kt = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',45)
                ->where('warehouse_movements.if_comodato', '=',0)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_5kc = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',5)
				->where('warehouse_movements.if_comodato', '=',1)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_10kc = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',10)
				->where('warehouse_movements.if_comodato', '=',1)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_15kc = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.presentacion','=',15)
				->where('warehouse_movements.if_comodato', '=',1)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_m15kc = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.presentacion','=',16)
				->where('warehouse_movements.if_comodato', '=',1)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_45kc = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',45)
                ->where('warehouse_movements.if_comodato', '=',1)
				->select('rest_devol')
				->sum('rest_devol');

                $sum_5kd = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',5)
				->where('containers.if_devol', '=',1)
				->select('devol')
				->sum('devol');

                $sum_10kd = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',10)
				->where('containers.if_devol', '=',1)
				->select('devol')
				->sum('devol');

                $sum_15kd = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.presentacion','=',15)
				->where('containers.if_devol', '=',1)
				->select('devol')
				->sum('devol');

                $sum_m15kd = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.presentacion','=',16)
				->where('containers.if_devol', '=',1)
				->select('devol')
				->sum('devol');

                $sum_45kd = ContainerDetail::leftjoin('containers', 'container_details.container_id', '=', 'containers.id') 
                ->leftjoin('warehouse_movements', 'containers.warehouse_movement', '=', 'warehouse_movement.id')
                ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')    
                ->leftjoin('clients', 'containers.client_id', '=', 'clients.id')                     
                ->leftjoin('articles', 'container_details.article_id', '=', 'articles.id')
				->where('containers.clients_id', '=', $facturation['client_id'])
                ->where('articles.convertion','=',45)
				->where('containers.if_devol', '=',1)
				->select('devol')
				->sum('devol');

				
				
             
			             
                $facturation->sum_5kt = $sum_5kt;
                $facturation->sum_10kt = $sum_10kt;
                $facturation->sum_15kt = $sum_15kt;
                $facturation->sum_45kt = $sum_45kt;
                $facturation->sum_m15t =$sum_m15kt;
                $facturation->sum_5kc = $sum_5kc;
                $facturation->sum_10kc = $sum_10kc;
                $facturation->sum_15kc = $sum_15kc;
                $facturation->sum_45kc = $sum_45kc; 
                $facturation->sum_m15c = $sum_m15kc;
                $facturation->sum_5kd = $sum_5kd;
                $facturation->sum_10kd = $sum_10kd;
                $facturation->sum_15kd = $sum_15kd;
                $facturation->sum_45kd = $sum_45kd;
                $facturation->sum_m15kd = $sum_m15kd;
                $facturation->sum_1kr = 0;
                $facturation->sum_5kr = 0;
                $facturation->sum_10kr = 0;
                $facturation->sum_15kr = 0;
                $facturation->sum_45kr = 0;
                $facturation->sum_m15kr = 0;

            
                $totals_sum_5kt += $facturation['sum_5kt'];
                $totals_sum_10kt += $facturation['sum_10kt'];
                $totals_sum_15kt += $facturation['sum_15kt'];
                $totals_sum_45kt += $facturation['sum_45kt'];
                $totals_sum_m15t +=$facturation['sum_m15kt'];
                $totals_sum_5kc += $facturation['sum_5kc'];
                $totals_sum_10kc += $facturation['sum_10kc'];
                $totals_sum_15kc += $facturation['sum_15kc'];
                $totals_sum_45kc += $facturation['sum_45kc']; 
                $totals_sum_m15c += $facturation['sum_m15kc'];
                $totals_sum_5kd += $facturation['sum_5kd'];
                $totals_sum_10kd += $facturation['sum_10kd'];
                $totals_sum_15kd += $facturation['sum_15kd'];
                $totals_sum_45kd += $facturation['sum_45kd'];
                $totals_sum_m15kd += $facturation['sum_m15kd'];
                $totals_sum_1kr += 0;
                $totals_sum_5kr += 0;
                $totals_sum_10kr += 0;
                $totals_sum_15kr += 0;
                $totals_sum_45kr += 0;
                $totals_sum_m15r += 0;
            
  
				$response[] = $facturation;

			}


		    $totals = new stdClass();
		
		    $totals->business_name = 'TOTAL';								
		    $totals->sum_5kt = $totals_sum_5kt;
            $totals->sum_10kt = $totals_sum_10kt;
            $totals->sum_15kt = $totals_sum_15kt;
            $totals->sum_45kt = $totals_sum_45kt;
            $totals->sum_m15t =$totals_sum_m15t;
            $totals->sum_5kc = $totals_sum_5kc;
            $totals->sum_10kc = $totals_sum_10kc;
            $totals->sum_15kc = $totals_sum_15kc;
            $totals->sum_45kc = $totals_sum_45kc; 
            $totals->sum_m15c = $totals_sum_m15c;
            $totals->sum_5kd = $totals_sum_5kd;
            $totals->sum_10kd = $totals_sum_10kd;
            $totals->sum_15kd = $totals_sum_15kd;
            $totals->sum_45kd = $totals_sum_45kd;
            $totals->sum_m15kd = $totals_sum_m15kd;
            $totals->sum_1kr = 0;
            $totals->sum_5kr = 0;
            $totals->sum_10kr = 0;
            $totals->sum_15kr = 0;
            $totals->sum_45kr = 0;
            $totals->sum_m15r = 0;
		    $response[] = $totals;


		 if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'REPORTE DE CONTROL DE MASA  DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);


          $sheet->mergeCells('B29');
		  $sheet->setCellValue('B29', 'CLIENTE');
		  $sheet->getStyle('B29')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '44546a')
			]
		]);
        $sheet->mergeCells('C30');
     //   $sheet->setCellValue('B30', '5K');
        $sheet->getStyle('C30')->applyFromArray([
          'font' => [
              'color' => array('rgb' => 'FFFFFF'),
              'bold' => true,
              'size' => 12,
          ],
          'alignment' => [
              'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
          ],
          'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => array('rgb' => '44546a')
          ]
      ]);
      $sheet->mergeCells('D30');
      //$sheet->setCellValue('C30', '10K');
      $sheet->getStyle('D30')->applyFromArray([
        'font' => [
            'color' => array('rgb' => 'FFFFFF'),
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => array('rgb' => '44546a')
        ]
    ]);
    $sheet->mergeCells('E30');
    //$sheet->setCellValue('E30', '15K');
    $sheet->getStyle('E30')->applyFromArray([
      'font' => [
          'color' => array('rgb' => 'FFFFFF'),
          'bold' => true,
          'size' => 12,
      ],
      'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
      ],
      'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => array('rgb' => '44546a')
      ]
  ]);
  $sheet->mergeCells('F30');
  //$sheet->setCellValue('F30', '45K');
  $sheet->getStyle('F30')->applyFromArray([
    'font' => [
        'color' => array('rgb' => 'FFFFFF'),
        'bold' => true,
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => '44546a')
    ]
]);
$sheet->mergeCells('G30');
//$sheet->setCellValue('G30', 'M-15');
$sheet->getStyle('G30')->applyFromArray([
  'font' => [
      'color' => array('rgb' => 'FFFFFF'),
      'bold' => true,
      'size' => 12,
  ],
  'alignment' => [
      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
  ],
  'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => array('rgb' => '44546a')
  ]
]);


	      $sheet->mergeCells('C29:G29');
		  $sheet->setCellValue('C29', ' TRÁNSITOS ');
		  $sheet->getStyle('C29')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '44546a')
			]
		]);

        $sheet->mergeCells('H30');
        //$sheet->setCellValue('H30', '5K');
        $sheet->getStyle('H30')->applyFromArray([
          'font' => [
              'color' => array('rgb' => 'FFFFFF'),
              'bold' => true,
              'size' => 12,
          ],
          'alignment' => [
              'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
          ],
          'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => array('rgb' => '44546a')
          ]
      ]);
      $sheet->mergeCells('I30');
    //  $sheet->setCellValue('I30', '10K');
      $sheet->getStyle('I30')->applyFromArray([
        'font' => [
            'color' => array('rgb' => 'FFFFFF'),
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => array('rgb' => '44546a')
        ]
    ]);
    $sheet->mergeCells('J30');
    //$sheet->setCellValue('J30', '15K');
    $sheet->getStyle('J30')->applyFromArray([
      'font' => [
          'color' => array('rgb' => 'FFFFFF'),
          'bold' => true,
          'size' => 12,
      ],
      'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
      ],
      'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => array('rgb' => '44546a')
      ]
  ]);
  $sheet->mergeCells('K30');
  //$sheet->setCellValue('K30', '45K');
  $sheet->getStyle('K30')->applyFromArray([
    'font' => [
        'color' => array('rgb' => 'FFFFFF'),
        'bold' => true,
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => '44546a')
    ]
]);
$sheet->mergeCells('L30');
//$sheet->setCellValue('L30', 'M-15');
$sheet->getStyle('L30')->applyFromArray([
  'font' => [
      'color' => array('rgb' => 'FFFFFF'),
      'bold' => true,
      'size' => 12,
  ],
  'alignment' => [
      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
  ],
  'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => array('rgb' => '44546a')
  ]
]);

		$sheet->mergeCells('H29:L29');
		$sheet->setCellValue('H29', ' COMODATOS ');
		$sheet->getStyle('H29')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1E90FF')
			]
		]);

        $sheet->mergeCells('M30');
        //$sheet->setCellValue('M30', '5K');
        $sheet->getStyle('M30')->applyFromArray([
          'font' => [
              'color' => array('rgb' => 'FFFFFF'),
              'bold' => true,
              'size' => 12,
          ],
          'alignment' => [
              'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
          ],
          'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => array('rgb' => '44546a')
          ]
      ]);
      $sheet->mergeCells('N30');
      //$sheet->setCellValue('N30', '10K');
      $sheet->getStyle('N30')->applyFromArray([
        'font' => [
            'color' => array('rgb' => 'FFFFFF'),
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => array('rgb' => '44546a')
        ]
    ]);
    $sheet->mergeCells('O30');
    //$sheet->setCellValue('O30', '15K');
    $sheet->getStyle('O30')->applyFromArray([
      'font' => [
          'color' => array('rgb' => 'FFFFFF'),
          'bold' => true,
          'size' => 12,
      ],
      'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
      ],
      'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => array('rgb' => '44546a')
      ]
  ]);
  $sheet->mergeCells('P30');
  //$sheet->setCellValue('P30', '45K');
  $sheet->getStyle('P30')->applyFromArray([
    'font' => [
        'color' => array('rgb' => 'FFFFFF'),
        'bold' => true,
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => '44546a')
    ]
]);
$sheet->mergeCells('Q30');
//$sheet->setCellValue('Q30', 'M-15');
$sheet->getStyle('Q30')->applyFromArray([
  'font' => [
      'color' => array('rgb' => 'FFFFFF'),
      'bold' => true,
      'size' => 12,
  ],
  'alignment' => [
      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
  ],
  'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => array('rgb' => '44546a')
  ]
]);

		$sheet->mergeCells('M29:Q29');
		$sheet->setCellValue('M29', ' DEPOSITADOS ');
		$sheet->getStyle('M29')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1E90FF')
			]
		]);

        $sheet->mergeCells('R29:V29');
		$sheet->setCellValue('R29', ' OTROS ');
		$sheet->getStyle('R29')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1E90FF')
			]
		]);

        $sheet->mergeCells('R30');
        //$sheet->setCellValue('R30', '5K');
        $sheet->getStyle('R30')->applyFromArray([
          'font' => [
              'color' => array('rgb' => 'FFFFFF'),
              'bold' => true,
              'size' => 12,
          ],
          'alignment' => [
              'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
          ],
          'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => array('rgb' => '44546a')
          ]
      ]);
      $sheet->mergeCells('S30');
    //  $sheet->setCellValue('S30', '10K');
      $sheet->getStyle('S30')->applyFromArray([
        'font' => [
            'color' => array('rgb' => 'FFFFFF'),
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => array('rgb' => '44546a')
        ]
    ]);
    $sheet->mergeCells('T30');
  //  $sheet->setCellValue('T30', '15K');
    $sheet->getStyle('T30')->applyFromArray([
      'font' => [
          'color' => array('rgb' => 'FFFFFF'),
          'bold' => true,
          'size' => 12,
      ],
      'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
      ],
      'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => array('rgb' => '44546a')
      ]
  ]);
  $sheet->mergeCells('U30');
//  $sheet->setCellValue('U30', '45K');
  $sheet->getStyle('U30')->applyFromArray([
    'font' => [
        'color' => array('rgb' => 'FFFFFF'),
        'bold' => true,
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => '44546a')
    ]
]);
   $sheet->mergeCells('V30');
  //$sheet->setCellValue('V30', 'M-15');
   $sheet->getStyle('V30')->applyFromArray([
  'font' => [
      'color' => array('rgb' => 'FFFFFF'),
      'bold' => true,
      'size' => 12,
  ],
  'alignment' => [
      'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
  ],
  'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => array('rgb' => '44546a')
  ]
]);

		

			$sheet->setCellValue('A30', '#');
			$sheet->setCellValue('B30', '   ');
			$sheet->setCellValue('C30', '5K');
			$sheet->setCellValue('D30', '10K');	
			$sheet->setCellValue('E30', '15K');
			$sheet->setCellValue('F30', '45K');
			$sheet->setCellValue('G30', 'M15');
			$sheet->setCellValue('H30', '5K');
			$sheet->setCellValue('I30', '10K');
			$sheet->setCellValue('J30', '15K');
			$sheet->setCellValue('K30', '45K');
			$sheet->setCellValue('L30', 'M-15');
			$sheet->setCellValue('M30', '5K');
			$sheet->setCellValue('N30', '10K');
			$sheet->setCellValue('O30', '15K');
			$sheet->setCellValue('P30', '45K');
			$sheet->setCellValue('Q30', 'M-15');
			$sheet->setCellValue('R30', '5K');
			$sheet->setCellValue('S30', '10K');
			$sheet->setCellValue('T30', '15K');
			$sheet->setCellValue('U30', '45K');
			$sheet->setCellValue('V30', 'M-15');
			$sheet->getStyle('A30:V30')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 31;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->business_name);
				$sheet->setCellValue('C'.$row_number, $element->sum_5kt);
                $sheet->setCellValue('D'.$row_number, $element->sum_10kt);
				$sheet->setCellValue('E'.$row_number, $element->sum_15kt);
                $sheet->setCellValue('F'.$row_number, $element->sum_45kt);               
				$sheet->setCellValue('G'.$row_number, $element->sum_m15t);
                $sheet->setCellValue('H'.$row_number, $element->sum_5kc);
                $sheet->setCellValue('I'.$row_number, $element->sum_10kc);
                $sheet->setCellValue('J'.$row_number, $element->sum_15kc);
				$sheet->setCellValue('K'.$row_number, $element->sum_45kc); 
				$sheet->setCellValue('L'.$row_number, $element->sum_m15c);
				$sheet->setCellValue('M'.$row_number, $element->sum_5kd);
				$sheet->setCellValue('N'.$row_number, $element->sum_10kd);
				$sheet->setCellValue('O'.$row_number, $element->sum_15kd);
				$sheet->setCellValue('P'.$row_number, $element->sum_45kd);
				$sheet->setCellValue('Q'.$row_number, $element->sum_m15kd);
				$sheet->setCellValue('R'.$row_number, $element->sum_5kr); 
				$sheet->setCellValue('S'.$row_number, $element->sum_10kr);
				$sheet->setCellValue('T'.$row_number, $element->sum_15kr);
				$sheet->setCellValue('U'.$row_number, $element->sum_45kr);
				$sheet->setCellValue('V'.$row_number, $element->sum_m15r);


               
							
            //    $sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');
			//	$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0.00');
			//	$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
			$sheet->getColumnDimension('U')->setAutoSize(true);
            $sheet->getColumnDimension('V')->setAutoSize(true);
			
			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








