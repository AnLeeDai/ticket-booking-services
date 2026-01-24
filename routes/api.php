<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('logout-all-devices', [AuthController::class, 'logoutAll'])->middleware('auth:sanctum');
    Route::post('logout/{deviceName}/device', [AuthController::class, 'logoutDevice'])->middleware('auth:sanctum');
    Route::get('devices', [AuthController::class, 'getAllUserLoginDevices'])->middleware('auth:sanctum');
});
