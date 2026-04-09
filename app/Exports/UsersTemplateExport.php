<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersTemplateExport implements WithHeadings
{
    public function headings(): array
    {
        return [
            'Nombre',
            'Correo Electrónico',
            'Teléfono',
            'Contraseña',
            'Rol',
        ];
    }
}
