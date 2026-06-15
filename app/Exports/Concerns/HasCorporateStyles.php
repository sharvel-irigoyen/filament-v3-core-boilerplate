<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Estilos corporativos compartidos por todos los exports.
 *
 * Uso: `use HasCorporateStyles;` en cualquier Export que implemente WithStyles.
 */
trait HasCorporateStyles
{
    /**
     * Aplica encabezado azul corporativo + bordes a toda la hoja.
     */
    protected function applyCorporateStyles(Worksheet $sheet): void
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // ► Encabezado: azul corporativo, texto blanco, negrita
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill'      => ['fillType' => 'solid', 'color' => ['rgb' => '0066CC']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // ► Bordes finos en todo el rango (encabezado + datos)
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")
              ->getBorders()->getAllBorders()->setBorderStyle('thin');
    }

    /**
     * Aplica estilo "fila de ejemplo" (gris claro, itálica) a un rango.
     */
    protected function applyExampleRowStyle(Worksheet $sheet, string $lastCol, int $row): void
    {
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F9FAFB']],
        ]);
    }
}
