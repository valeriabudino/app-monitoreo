<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServidorController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/servidor', [ServidorController::class, 'index']);
Route::get('/api/metrics', [ServidorController::class, 'api']);