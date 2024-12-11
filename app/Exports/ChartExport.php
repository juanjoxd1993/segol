<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class ChartExport implements FromCollection, WithHeadings, WithCharts, WithEvents
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        $data = $this->collection->toArray();

        $processedData = [['', ''], ['', ''], ['', ''], ['Fechas', 'Kilos']];

        foreach ($data as $row) {
            $processedData[] = [
                $row['fecha'],
                $row['cantidad'],
            ];
        }

        return collect($processedData);
    }

    public function headings(): array
    {
        return [''];
    }

    public function charts()
    {
        $rowCount = $this->collection->count();

        $categories = new DataSeriesValues('String', 'Worksheet!$A$5:$A$' . ($rowCount + 4), null, $rowCount);
        $values = new DataSeriesValues('Number', 'Worksheet!$B$5:$B$' . ($rowCount + 4), null, $rowCount);

        $valuesArray = [$values];

        $dataLabels = [];
        foreach ($this->collection as $row) {
            $dataLabels[] = new DataSeriesValues('String', '="' . $row['cantidad'] . '"', null, 0);
        }

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,       // Tipo de gráfico: Barra
            DataSeries::GROUPING_CLUSTERED,  // Agrupación de las barras
            range(0, count($valuesArray) - 1), // Rango de las series de datos
            $dataLabels,                     // Activar las etiquetas de datos
            [$categories],                   // Categorías del eje X
            $valuesArray                     // Datos del eje Y
        );

        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        $plotArea = new PlotArea(null, [$series]);

        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        $chart = new Chart(
            'sample_chart',                  // Nombre del gráfico
            new Title('Reporte de Venta Diario en Kilos'), // Título del gráfico
            null,                          // Leyenda
            $plotArea                         // Área del gráfico
        );

        $chart->setTopLeftPosition('D10');
        $chart->setBottomRightPosition('P30');

        return $chart;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->mergeCells('D1:G1');
                $sheet->setCellValue('D1', 'Reporte de Venta Diario en Kilos');
                $sheet->getStyle('D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D1')->getFont()->setSize(18)->setBold(true);

                $sheet->setCellValue('D2', 'Presentación');
                $sheet->setCellValue('E2', 'Cantidad');
                $sheet->setCellValue('F2', 'Total Kilos');
                $sheet->setCellValue('G2', 'Total Soles');

                $sheet->getStyle('D2:G2')->getFont()->setBold(true);

                $sheet->setCellValue('D3', 'Balón 10k');
                $sheet->setCellValue('E3', '11');
                $sheet->setCellValue('F3', '222');
                $sheet->setCellValue('G3', '333');

                $sheet->setCellValue('D4', 'Balón 45k');
                $sheet->setCellValue('E4', '66666');
                $sheet->setCellValue('F4', '7777');
                $sheet->setCellValue('G4', '88888');

                $sheet->setCellValue('D5', '');
                $sheet->setCellValue('E5', '1000');
                $sheet->setCellValue('F5', '34444');
                $sheet->setCellValue('G5', '5555');

                $sheet->getStyle('G3:G5')->getNumberFormat()->setFormatCode('[$S/ ]#,##0.00');

                $sheet->getStyle('D2:G5')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
