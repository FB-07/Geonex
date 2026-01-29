<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\PlaceSearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/places/search', [PlaceSearchController::class, 'search']);

Route::get('/distritos', [LocationController::class, 'distritos']);
Route::get('/municipios', [LocationController::class, 'municipios']);
Route::get('/freguesias', [LocationController::class, 'freguesias']);
