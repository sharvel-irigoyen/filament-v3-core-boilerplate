<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure, ShouldQueue, WithChunkReading
{
    use SkipsFailures;

    public function chunkSize(): int
    {
        return 100;
    }

    /**
    * @param Row $row
    */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        \Illuminate\Support\Facades\Log::info("UsersImport [SYNC]: Processing Row {$rowIndex}", $row);

        try {
            $user = User::updateOrCreate(
                ['email' => $row['correo_electronico']],
                [
                    'name'     => $row['nombre'],
                    'password' => isset($row['contrasena']) ? Hash::make($row['contrasena']) : null,
                    'phone'    => $row['telefono'] ?? null,
                ]
            );

            if (isset($row['contrasena']) && !empty($row['contrasena'])) {
                $user->password = Hash::make($row['contrasena']);
                $user->save();
            }

            if (isset($row['rol'])) {
                $roleName = $row['rol'];
                $role = Role::where('name', 'LIKE', $roleName)->first();
                if ($role) {
                    $user->syncRoles([$role->name]);
                } else {
                     \Illuminate\Support\Facades\Log::warning("UsersImport [SYNC]: Role {$roleName} not found for user {$user->email}");
                }
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error("UsersImport [SYNC]: Error processing row {$rowIndex}: " . $e->getMessage());
             throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required',
            'correo_electronico' => 'required|email',
            'contrasena' => 'sometimes',
            'rol' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'role.exists' => 'El rol especificado no existe. Roles disponibles: ' . Role::pluck('name')->implode(', '),
        ];
    }
}
