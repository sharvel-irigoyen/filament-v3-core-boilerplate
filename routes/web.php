<?php

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Http\Middleware\SetUpPanel;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware([
    'web',
    SetUpPanel::class . ':admin',
    FilamentAuthenticate::class,
    DispatchServingFilamentEvent::class,
])->group(function () {

});
