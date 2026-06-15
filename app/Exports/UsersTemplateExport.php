<?php

namespace App\Exports;

use App\Exports\Concerns\HasCorporateStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersTemplateExport implements WithHeadings, WithStyles, WithColumnWidths
{
    use HasCorporateStyles;

    public function headings(): array
    {
        return [
            'Nombre',
            'Correo Electrónico',
            'Unidad de Negocio',
            'Rol',
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        // ► Estilos corporativos (header azul + bordes)
        $this->applyCorporateStyles($sheet);

        // ► Fila de ejemplo para guiar al usuario
        $sheet->fromArray([
            ['Juan Pérez', 'juan.perez@empresa.com', 'On Empresas', 'panel_user'],
        ], null, 'A2');

        $this->applyExampleRowStyle($sheet, 'D', 2);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Nombre
            'B' => 35,  // Correo Electrónico
            'C' => 22,  // Unidad de Negocio
            'D' => 20,  // Rol
        ];
    }
}


