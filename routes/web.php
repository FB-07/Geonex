<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Middleware\Language;
use App\Http\Controllers\Web\MapController;
use App\Http\Controllers\Web\UsersController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\PointsInterestController;
use App\Http\Controllers\Web\DistritoController;
use App\Http\Controllers\Web\MunicipioController;
use App\Http\Controllers\Web\FreguesiaController;
use App\Http\Controllers\Web\CodigoPostalController;
use App\Http\Controllers\Web\PlaceController;

Route::get('', fn() => to_route('map.index'));

Route::middleware(['web', Language::class])->group(function () {

    Route::get('/change-language/{lang}', [AuthController::class, 'changeLang']);
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
    Route::get('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::middleware('auth')->group(function () {

        Route::resource('map', MapController::class)->only('index');

        Route::resource('users', UsersController::class);
        Route::singleton('profile', ProfileController::class);

        Route::resource('distritos', DistritoController::class)
            ->only(['index','edit','update','destroy']);
        Route::resource('municipios', MunicipioController::class)
            ->only(['index','edit','update','destroy']);
        Route::resource('freguesias', FreguesiaController::class)
            ->only(['index','edit','update','destroy']);
        Route::resource('codigos-postais', CodigoPostalController::class)
            ->only(['index','edit','update','destroy'])
            ->parameters(['codigos-postais' => 'codigo_postal']);
        Route::resource('places', PlaceController::class)
            ->only(['index','edit','update','destroy']);

        Route::resource('interest', PointsInterestController::class)
            ->only(['index', 'create', 'store', 'destroy']);

        Route::post('/interest/check', [PointsInterestController::class, 'check'])->name('interest.check');
        Route::get('/interest/{nome}/edit', [PointsInterestController::class, 'edit'])->name('interest.edit');
        Route::put('/interest/{nome}', [PointsInterestController::class, 'update'])->name('interest.update');

        Route::get('/pontos-interesse/names', [PointsInterestController::class, 'names']);
        Route::post('/pontos-interesse/places', [PointsInterestController::class, 'placesByName']);
    });
});

Route::fallback(function () {
    return view('404/index');
});