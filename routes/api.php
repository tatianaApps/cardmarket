<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CardsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['apitoken','permissions'])->prefix('cards')->group(function(){
    Route::put('/registerCollections',[CardsController::class, 'registerCollections']);
    Route::put('/registerCards',[CardsController::class, 'registerCards']);
});

Route::middleware(['apitoken','permissionsSales'])->prefix('cards')->group(function(){
    Route::post('/searchCards',[CardsController::class, 'searchCards']);
    Route::put('/sellCards',[CardsController::class, 'sellCards']);
});

Route::post('/login',[UsersController::class,'login']);
Route::post('/buyCards',[CardsController::class,'buyCards']);

Route::prefix('users')->group(function(){
    Route::put('/registerUser',[UsersController::class,'registerUser']);
    Route::post('/recoverPassword',[UsersController::class,'recoverPassword']);
});


