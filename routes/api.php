<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

Route::middleware('api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::resource('usuario',[UserController::class]);

// api/v1/usuario
Route::group(['prefix' => 'v1'], function () {
    Route::apiResource('usuario', UserController::class);   
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', [AuthController::class, 'me']);
});