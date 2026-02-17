<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Middleware\Language;
use App\Http\Controllers\Web\MapController;
use App\Http\Controllers\Web\UsersController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TecnicalController;
use App\Http\Controllers\Web\SaveInterestController;
use App\Http\Controllers\Web\PointsInterestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('', fn() => to_route('map.index'));

//Route::get('/mapa', [MapController::class,'index'])->name('mapa');

Route::middleware(['web', Language::class])->group(function () {
    Route::get('/change-language/{lang}', [AuthController::class, 'changeLang']);
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
    Route::get('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::group(['middleware' => 'auth'], function () {
        Route::resource('map',MapController::class)->only('index');
        Route::resource('/users', UsersController::class);
        Route::singleton('/profile', ProfileController::class);
        
        Route::get('/tec/distritos', [TecnicalController::class,'distritosview'])->name('tec-distritos');
        Route::get('/tec/municipios', [TecnicalController::class,'municipiosview'])->name('tec-municipios');
        Route::get('/tec/freguesias', [TecnicalController::class,'freguesiasview'])->name('tec-freguesias');
        Route::get('/tec/places', [TecnicalController::class,'placesview'])->name('tec-places');
        Route::get('/tec/cp', [TecnicalController::class,'cpview'])->name('tec-cp');
        Route::get('/interest', [PointsInterestController::class,'interestview'])->name('interest');
        Route::get('/interest/add', [PointsInterestController::class,'interestaddview'])->name('interest-add');
        Route::post('/interest/add', [SaveInterestController::class,'store'])->name('interest-controller');
        Route::post('/interest/check', [SaveInterestController::class, 'check'])->name('interest-check');
    });
});

Route::fallback(function () {
    return view('404/index');
});


