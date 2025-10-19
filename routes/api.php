<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::get('/upgradeUser/{id}', [SuperAdminController::class, 'upgradeUser']);
    Route::get('/downgradeUser/{id}', [SuperAdminController::class, 'downgradeUser']);
});
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {});
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {});
