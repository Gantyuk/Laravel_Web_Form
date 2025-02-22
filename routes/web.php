<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoController;

Route::get('/', function () {
    return view('app');
});
Route::post('/create-deal-account', [ZohoController::class, 'store']);
