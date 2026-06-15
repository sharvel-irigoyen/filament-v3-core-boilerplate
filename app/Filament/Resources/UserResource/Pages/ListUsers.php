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
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/private/' . $data['attachment']);

                    \Illuminate\Support\Facades\Log::info('ListUsers: Import Action Triggered', [
                        'attachment' => $data['attachment'],
                        'resolved'  => $filePath,
                    ]);

                    if (!file_exists($filePath)) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error al importar')
                            ->body('No se encontró el archivo subido. Intenta de nuevo.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $import = new \App\Imports\UsersImport;
                    try {
                        $import->import($filePath);
                    } catch (\Exception $e) {
                         \Filament\Notifications\Notification::make()
                            ->title('Error crítico en la importación')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        return;
                    }

                    if ($import->failures()->isNotEmpty()) {
                        $errorItems = [];
                        foreach ($import->failures() as $failure) {
                            $row = $failure->row();
                            $errors = implode(', ', $failure->errors());
                            $errorItems[] = "<li><strong>Fila {$row}:</strong> {$errors}</li>";
                        }
                        
                        $html = '<ul style="margin-top: 0.5rem; list-style-type: disc; padding-left: 1.5rem; max-height: 150px; overflow-y: auto;">' . implode('', $errorItems) . '</ul>';

                        \Filament\Notifications\Notification::make()
                            ->title('Importación completada con errores')
                            ->body(new \Illuminate\Support\HtmlString("Algunas filas no pudieron ser importadas:" . $html))
                            ->danger()
                            ->persistent()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Importación exitosa')
                            ->body('Todos los usuarios fueron importados correctamente.')
                            ->success()
                            ->send();
                    }

                    return redirect(UserResource::getUrl('index'));
                }),
            CreateAction::make(),
        ];
    }
}
