<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('logout-all-devices', [AuthController::class, 'logoutAll'])->middleware('auth:sanctum');
    Route::post('logout/{deviceName}/device', [AuthController::class, 'logoutDevice'])->middleware('auth:sanctum');
    Route::get('devices', [AuthController::class, 'getAllUserLoginDevices'])->middleware('auth:sanctum');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
});


// test role and permission
