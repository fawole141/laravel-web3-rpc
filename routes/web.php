<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BalanceController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/balance/{wallet}', [BalanceController::class, 'show']);
Route::get('/balance/{wallet}/{token}', [BalanceController::class, 'tokenBalance']);