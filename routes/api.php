<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ChallengeController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::resource('usuario',[UserController::class]);

// api/v1/usuario
Route::group(['prefix' => 'v1'], function () {
    Route::apiResource('usuario', UserController::class);   
    Route::apiResource('professor', TeacherController::class);   
    Route::apiResource('projeto', ProjectController::class);   
    Route::apiResource('convite', InvitationController::class);   
    Route::apiResource('desafio', ChallengeController::class);   
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);   
    Route::post('/logout', [AuthController::class, 'logout']);   
    Route::post('/create/teacher', [AuthController::class, 'registerTeacher']);   
    Route::get('/profile', [AuthController::class, 'profile']);   
});