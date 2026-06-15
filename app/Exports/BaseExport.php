<?php

namespace App\Exports;

use App\Exports\Concerns\HasCorporateStyles;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BaseExport extends ExcelExport implements WithStyles
{
    use HasCorporateStyles;

    /**  Estilos globales de la hoja  */
    public function styles(Worksheet $sheet)
    {
        $this->applyCorporateStyles($sheet);
    }
}

