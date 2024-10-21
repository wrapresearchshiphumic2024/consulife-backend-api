<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PsychologistController;

Route::post('register', [AuthController::class, 'register']);
Route::post('register/psychologist', [AuthController::class, 'RegisterPsychologist']);
Route::post('login', [AuthController::class, 'login']);


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::middleware('role:psychologists')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
    });

    Route::middleware('role:patient')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
    });

    Route::get('/admin/home', [AdminController::class, 'index']);
    Route::post('logout', [AuthController::class, 'logout']);
});
