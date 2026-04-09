<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return trans('filament-users::user.resource.title.list');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('downloadTemplate')
                ->label('Descargar Plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UsersTemplateExport, 'usuarios_plantilla.xlsx')),

            \Filament\Actions\Action::make('import')
                ->label('Importar Usuarios')
                ->icon('heroicon-o-arrow-up-tray')
                ->closeModalByClickingAway(false)
                ->form([
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('Archivo Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    \Illuminate\Support\Facades\Log::info('ListUsers: Import Action Triggered (ASYNC)', ['attachment' => $data['attachment']]);

                    $import = new \App\Imports\UsersImport;
                    try {
                        \Maatwebsite\Excel\Facades\Excel::import($import, $data['attachment']);
                    } catch (\Exception $e) {
                         \Filament\Notifications\Notification::make()
                            ->title('Error al iniciar importación')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        return;
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Importación iniciada')
                        ->icon('heroicon-o-paper-airplane')
                        ->body('El proceso se está ejecutando en segundo plano. Te notificaremos cuando termine.')
                        ->success()
                        ->send();

                    return redirect(UserResource::getUrl('index'));
                }),
            CreateAction::make(),
        ];
    }
}
