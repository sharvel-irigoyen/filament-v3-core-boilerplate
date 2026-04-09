<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    public static function getNavigationLabel(): string
    {
        return trans('filament-users::user.resource.label');
    }

    public static function getPluralLabel(): string
    {
        return trans('filament-users::user.resource.label');
    }

    public static function getLabel(): string
    {
        return trans('filament-users::user.resource.single');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-users.group');
    }

    public function getTitle(): string
    {
        return trans('filament-users::user.resource.title.resource');
    }

    public static function form(Form $form): Form
    {
        $rows = [
            TextInput::make('name')
                ->required()
                ->label(trans('filament-users::user.resource.name')),
            TextInput::make('email')
                ->email()
                ->required()
                ->label(trans('filament-users::user.resource.email')),
            TextInput::make('phone')
                ->tel()
                ->label('Teléfono'),
            TextInput::make('password')
                ->label(trans('filament-users::user.resource.password'))
                ->password()
                ->maxLength(255)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrateStateUsing(static function ($state, $record) use ($form) {
                    return !empty($state)
                        ? Hash::make($state)
                        : ($record ? $record->password : null);
                }),
        ];


        if (config('filament-users.shield') && class_exists(\BezhanSalleh\FilamentShield\FilamentShield::class)) {
            $rows[] = Forms\Components\Select::make('roles')
                ->multiple()
                ->preload()
                ->relationship('roles', 'name', modifyQueryUsing: function (Builder $query) {
                    if (!auth()->user()->hasRole('super_admin')) {
                        $query->where('name', '!=', 'super_admin');
                    }
                })
                ->label(trans('filament-users::user.resource.roles'))
                ->rules([
                    function () {
                        return function (string $attribute, $value, \Closure $fail) {
                            if (auth()->user()->hasRole('super_admin')) {
                                return;
                            }

                            // Value might be ID or array of IDs
                            $ids = is_array($value) ? $value : [$value];

                            $forbiddenRoles = \Spatie\Permission\Models\Role::whereIn('id', $ids)
                                ->where('name', 'super_admin')
                                ->exists();

                            if ($forbiddenRoles) {
                                $fail('No tienes permisos para asignar el rol de Super Admin.');
                            }
                        };
                    },
                ]);
        }

        $form->schema($rows);

        return $form;
    }

    public static function table(Table $table): Table
    {
        $actions = [
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];

        // Impersonate Action
        array_unshift($actions, \Filament\Tables\Actions\Action::make('impersonate')
            ->label('Impersonar')
            ->icon('heroicon-o-user-group')
            ->action(function ($record) {
                if ($record->is(\Illuminate\Support\Facades\Auth::user())) {
                    return;
                }
                session()->put([
                    'impersonate.back_to' => filament()->getCurrentPanel()->getUrl(),
                    'impersonate.guard' => filament()->getCurrentPanel()->getAuthGuard(),
                ]);
                app(\Lab404\Impersonate\Services\ImpersonateManager::class)->take(
                    \Illuminate\Support\Facades\Auth::user(),
                    $record,
                    filament()->getCurrentPanel()->getAuthGuard()
                );
                return redirect(request()->header('Referer') ?? filament()->getCurrentPanel()->getUrl());
            })
            ->visible(fn ($record) =>
                auth()->user()->hasRole('super_admin') &&
                !$record->is(auth()->user()) &&
                $record->canAccessPanel(filament()->getCurrentPanel())
            )
            ->tooltip('Impersonar')
        );

        $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label(trans('filament-users::user.resource.id')),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label(trans('filament-users::user.resource.name')),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->label(trans('filament-users::user.resource.email')),
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Role')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                IconColumn::make('email_verified_at')
                    ->boolean()
                    ->sortable()
                    ->searchable()
                    ->label(trans('filament-users::user.resource.email_verified_at')),
                TextColumn::make('created_at')
                    ->label(trans('filament-users::user.resource.created_at'))
                    ->dateTime('M j, Y')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(trans('filament-users::user.resource.updated_at'))
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->label(trans('filament-users::user.resource.verified'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('unverified')
                    ->label(trans('filament-users::user.resource.unverified'))
                    ->query(fn(Builder $query): Builder => $query->whereNull('email_verified_at')),
            ])
            ->actions([
                ActionGroup::make($actions),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        return $table;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->hasRole('super_admin')) {
            $query->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super_admin');
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
