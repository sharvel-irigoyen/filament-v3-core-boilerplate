<?php

namespace App\Imports;

use App\Models\User;
use App\Services\PasswordService;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Row;
use Spatie\Permission\Models\Role;

use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure, WithChunkReading
{
    use Importable, SkipsFailures;

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
            $isNewUser     = !User::where('email', $row['correo_electronico'])->exists();
            $plainPassword = PasswordService::generate();

            $user = User::updateOrCreate(
                ['email' => $row['correo_electronico']],
                [
                    'name'          => $row['nombre'],
                    'password'      => Hash::make($plainPassword),
                ]
            );

            if (isset($row['rol'])) {
                $roleName = $row['rol'];
                $role = Role::where('name', 'LIKE', $roleName)->first();
                if ($role) {
                    $user->syncRoles([$role->name]);
                } else {
                     \Illuminate\Support\Facades\Log::warning("UsersImport [SYNC]: Role {$roleName} not found for user {$user->email}");
                }
            }

            // Enviar email de bienvenida solo a usuarios nuevos
            if ($isNewUser) {
                PasswordService::notifyWelcome($user, $plainPassword);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error("UsersImport [SYNC]: Error processing row {$rowIndex}: " . $e->getMessage());
             throw $e;
        }
    }

    public function prepareForValidation($data, $index)
    {
        return $data;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required',
            'correo_electronico' => 'required|email',
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

