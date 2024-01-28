<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ExcelStyleService
{
    public static function baseFontStyle($backgroundColor)
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // Color de la fuente
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $backgroundColor], // Color de fondo personalizable
            ]
        ];
    }


    public static function baseAlignmentStyle()
    {
        return [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ]
        ];
    }

    public static function addImageToSheet($sheet, $imagePath, $coordinates, $width, $height, $offsetY = 0)
    {
        $sheet->getRowDimension(1)->setRowHeight($height);
        $drawing = new Drawing();
        $drawing->setPath($imagePath);
        $drawing->setCoordinates($coordinates);
        $drawing->setResizeProportional(false);
        $drawing->setWidth($width);
        $drawing->setHeight($height);
        $drawing->setOffsetY($offsetY);
        $drawing->setWorksheet($sheet);
    }

    public static function cabezeraEstilos($sheet, $color='FF722CFF')
    {
        $sheet->getRowDimension('3')->setRowHeight(40);
        // Calcula la última columna con contenido
        $highestColumn = $sheet->getHighestColumn();

        // Aplicar estilos de fuente y alineación a la fila 3
        $styleArray = self::baseFontStyle($color);
        $sheet->getStyle('A3:' . $highestColumn . '3')->applyFromArray($styleArray);

        // Aplica los estilos de alineación al resto de las filas
        $highestRow = $sheet->getHighestRow();
        $styleArray2 = self::baseAlignmentStyle();
        $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray($styleArray2);

        // Añadir imagen
        $imagePath = public_path('backend/img/logo-dashboard.png');
        self::addImageToSheet($sheet, $imagePath, 'A1', 180, 60, 10);
    }
}
