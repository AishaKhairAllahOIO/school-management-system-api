<?php

use App\Http\Controllers\Auth\SystemAccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', [SystemAccessController::class,'login']) ;
Route::post('/verifyOtp', [SystemAccessController::class,'verifyOtp']);
Route::get('/logout', [SystemAccessController::class,'logout'])->middleware('auth:sanctum');


