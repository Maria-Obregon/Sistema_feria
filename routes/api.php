<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class,'register']);
Route::post('/login',    [AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (\Illuminate\Http\Request $r) => $r->user()->load('rol'));
    Route::post('/logout', [AuthController::class,'logout']);
});
