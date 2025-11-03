<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



//______allUSer____
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/cancelEvent/{id}',[EventController::class,'cancelEvent'])->middleware('auth:sanctum');
Route::post('/postponeEvent/{id}',[EventController::class,'postponeEvent'])->middleware('auth:sanctum');

Route::get('/showUpcommingEvent', [EventController::class, 'showUpcommingEvent']);

Route::post('/subscribe', [SubscriberController::class, 'subscribe']);


//______event_______
Route::post('addEvent', [EventController::class, 'addEvent'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::get('/showUsers', [SuperAdminController::class, 'showUsers']);
    Route::get('/upgradeUser/{id}', [SuperAdminController::class, 'upgradeUser']);
    Route::get('/downgradeUser/{id}', [SuperAdminController::class, 'downgradeUser']);
});
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {});
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {});

Route::middleware(['auth:sanctum', 'role:superadmin|admin'])->group(function () {

    Route::get('/showAllEventPending', [SuperAdminController::class, 'showAllEventPending']);
    Route::get('/showAllEventaccepted', [SuperAdminController::class, 'showAllEventaccepted']);
    Route::get('/showAllEventRejected', [SuperAdminController::class, 'showAllEventRejected']);

    Route::post('/approveEvent/{id}', [SuperAdminController::class, 'approveEvent']);
});

Route::get('/search/{keyword}', [EventController::class, 'search'])->name('events.search');
Route::get('/filterByType/{type}', [EventController::class, 'filterByType'])->name('events.type');
Route::get('/filterByAddress/{address}', [EventController::class, 'filterByAddress'])->name('events.address');
Route::get('/filterByDate/{date}', [EventController::class, 'filterByDate'])->name('events.date');

Route::get('/myEvents', [AuthController::class, 'myEvents'])->middleware('auth:sanctum');
