<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\User;
use App\Filament\Resources\UserResource;
use App\Services\PasswordService;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** Almacena la contraseña en texto plano antes de que Filament la hashee */
    private ?string $pendingPlainPassword = null;

    public function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['password'])) {
            // Capturar la contraseña plana para notificar por email.
            // El cast 'hashed' del modelo User se encarga de hashear al guardar.
            $this->pendingPlainPassword = $data['password'];
        }
        // Si el campo quedó vacío, dehydrated(false) lo excluye de $data,
        // por lo que Eloquent no toca la contraseña existente.

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->pendingPlainPassword) {
            PasswordService::notifyPasswordChanged(
                $this->record,
                $this->pendingPlainPassword,
            );
            $this->pendingPlainPassword = null;
        }
    }

    public function getTitle(): string
    {
        return trans('filament-users::user.resource.title.edit');
    }

    protected function getActions(): array
    {
        $actions = [];

        if (config('filament-users.impersonate')) {
            $actions[] = Impersonate::make()->record($this->getRecord());
        }

        $actions[] = DeleteAction::make();
        $actions[] = ForceDeleteAction::make();
        $actions[] = RestoreAction::make();

        return $actions;
    }
}
