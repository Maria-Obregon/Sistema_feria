<?php

use Illuminate\Support\Facades\Route;

// Todas las rutas del frontend Vue se manejan por la SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
